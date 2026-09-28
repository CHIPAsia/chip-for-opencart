<?php
namespace Opencart\Catalog\Model\Extension\Chip\Payment;

class Chip extends \Opencart\System\Engine\Model {
	const DUITNOW_GROUP = ['duitnow_qr', 'dnqr'];
	const SHOPEE_GROUP = ['razer_shopeepay', 'shopee_pay'];

	/**
	 * Card methods that can back a recurring charge.
	 *
	 * CHIP issues recurring tokens for card payments only, so this list must
	 * never be widened - no other payment method can be charged on a cycle.
	 */
	const RECURRING_CARD_METHODS = ['visa', 'mastercard', 'maestro'];

	/**
	 * Days after the due date to retry a failed renewal charge.
	 *
	 * Measured from the original due date, not from "now", so a cron that runs
	 * late cannot stretch the ladder.
	 */
	const RETRY_OFFSETS_DAYS = [1, 3, 5];

	private $private_key;
	private $brand_id;

	/**
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethod($address): array {
		$this->load->language('extension/chip/payment/chip');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_chip_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		// Subscriptions are supported: CHIP is deliberately NOT hidden when the
		// cart holds a subscription product, it is offered card-only instead.
		if (!$this->config->get('payment_chip_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'chip',
				'title'      => nl2br($this->config->get('payment_chip_payment_name_' . $this->config->get('config_language_id'))),
				'sort_order' => $this->config->get('payment_chip_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethods(array $address = []): array {
		$this->load->language('extension/chip/payment/chip');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_chip_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		// Subscriptions are supported: CHIP is deliberately NOT hidden when the
		// cart holds a subscription product, it is offered card-only instead.
		if (!$this->config->get('payment_chip_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$option_data['chip'] = [
				'code' => 'chip.chip',
				'name' => nl2br($this->config->get('payment_chip_payment_name_' . $this->config->get('config_language_id')))
			];

			if ($this->customer->getId()) {
				$tokens = $this->getTokens($this->customer->getId());

				foreach ($tokens as $token) {
					$option_data[$token['chip_token_id']] = [
						'code' => 'chip.' . $token['chip_token_id'],
						'name' => $this->language->get('text_card_use') . ' ' . $this->language->get('text_' . $token['type']) . ' ' . $token['card_number']
					];
				}
			}

			$method_data = [
				'code'       => 'chip',
				'name'       => nl2br($this->config->get('payment_chip_payment_name_' . $this->config->get('config_language_id'))),
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_chip_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 * @param string $private_key
	 * @param string $brand_id
	 *
	 * @return void
	 */
	public function set_keys($private_key, $brand_id): void {
		$this->private_key = $private_key;
		$this->brand_id = $brand_id;
	}

	/**
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function create_purchase($params) {
		return $this->call('POST', '/purchases/', $params);
	}

	/**
	 * @param string $purchase_id
	 *
	 * @return mixed
	 */
	public function get_purchase($purchase_id) {
		return $this->call('GET', "/purchases/{$purchase_id}/");
	}

	/**
	 * @param string $currency
	 * @param int    $amount
	 *
	 * @return mixed
	 */
	public function payment_methods($currency, $amount) {
		return $this->call('GET', "/payment_methods/?brand_id={$this->brand_id}&currency={$currency}&amount={$amount}");
	}

	/**
	 * @param array  $whitelist
	 * @param string $currency
	 * @param int    $amount
	 *
	 * @return array
	 */
	public function resolve_payment_method_whitelist($whitelist, $currency, $amount) {
		static $cache = [];

		// In-memory migration: legacy razer_shopeepay key -> shopee_pay (modern).
		// Keeps backward compatibility for merchants with the old key saved.
		if (in_array('razer_shopeepay', $whitelist) && !in_array('shopee_pay', $whitelist)) {
			$whitelist = array_map(function ($method) {
				return $method === 'razer_shopeepay' ? 'shopee_pay' : $method;
			}, $whitelist);
			$whitelist = array_values(array_unique($whitelist));
		}

		$groups = [
			'dnqr'   => self::DUITNOW_GROUP,
			'shopee' => self::SHOPEE_GROUP
		];

		// 1. Short-circuit: no group member configured -> return unchanged (no API call).
		$configured_groups = [];

		foreach ($groups as $group_key => $group) {
			if (count(array_intersect($whitelist, $group)) > 0) {
				$configured_groups[$group_key] = $group;
			}
		}

		if (count($configured_groups) == 0) {
			return $whitelist;
		}

		// 2. Expand all configured groups in-memory.
		$expanded = $whitelist;

		foreach ($configured_groups as $group) {
			$expanded = array_merge($expanded, $group);
		}

		$expanded = array_values(array_unique($expanded));

		// 3. Cache key: brand + currency + amount-bucket (round to 100-sen steps).
		$cache_key = 'chip_pm_' . md5($this->brand_id . '|' . $currency . '|' . intval($amount / 100));

		if (isset($cache[$cache_key])) {
			$available = $cache[$cache_key];
		} else {
			$response = $this->payment_methods($currency, $amount);

			if (!is_array($response) || !isset($response['available_payment_methods'])) {
				// 4. Fallback: return expanded whitelist unchanged if the API fails.
				return $expanded;
			}

			$available = $response['available_payment_methods'];
			$cache[$cache_key] = $available;
		}

		// 5. Resolve each configured group against what the merchant actually has.
		$resolved = [];

		foreach ($configured_groups as $group_key => $group) {
			$resolved_group = array_values(array_intersect($group, $available));

			if ($group_key == 'dnqr') {
				// dnqr wins when both are present.
				if (in_array('dnqr', $resolved_group)) {
					$resolved_group = array_values(array_diff($resolved_group, ['duitnow_qr']));
				}
			} elseif ($group_key == 'shopee') {
				// shopee_pay wins when both are present.
				if (in_array('shopee_pay', $resolved_group)) {
					$resolved_group = array_values(array_diff($resolved_group, ['razer_shopeepay']));
				}
			}

			$resolved = array_merge($resolved, $resolved_group);
		}

		// 6. Final: original non-group entries + resolved groups.
		$final = $expanded;

		foreach ($configured_groups as $group) {
			$final = array_values(array_diff($final, $group));
		}

		$final = array_merge($final, $resolved);

		return $final;
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function addReport(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "chip_report`
			(`customer_id`, `chip_id`, `order_id`, `status`, `amount`, `environment_type`, `date_added`)
			VALUES (" . (int)$data['customer_id'] . ", '" . $this->db->escape($data['chip_id']) . "', " . (int)$data['order_id'] . ",
			'" . $this->db->escape($data['status']) . "', '" . (float)$data['amount'] . "',
			'" . $this->db->escape($data['environment_type']) . "', NOW())");
	}

	/**
	 * @param string $chip_id
	 * @param string $status
	 *
	 * @return void
	 */
	public function updateReportStatus(string $chip_id, string $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "chip_report`
			SET `status` = '" . $this->db->escape($status) . "'
			WHERE `chip_id` = '" . $this->db->escape($chip_id) . "'");
	}

	/**
	 * @param int $order_id
	 *
	 * @return ?array
	 */
	public function getReportByOrderId(int $order_id): ?array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_report` WHERE `order_id` = " . (int)$order_id . " ORDER BY `date_added` DESC LIMIT 1");

		if ($query->num_rows) {
			return $query->row;
		}

		return null;
	}

	/**
	 * @param string $purchase_id
	 * @param string $token_id
	 *
	 * @return mixed
	 */
	public function chargeToken($purchase_id, $token_id) {
		$params = [
			'recurring_token' => $token_id
		];

		return $this->call('POST', "/purchases/{$purchase_id}/charge/", $params);
	}

	/**
	 * @param int $chip_token_id
	 *
	 * @return ?array
	 */
	public function getTokenByChipTokenId(int $chip_token_id): ?array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_token` WHERE `chip_token_id` = " . (int)$chip_token_id);

		if ($query->num_rows) {
			return $query->row;
		}

		return null;
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function addToken(array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "chip_token`
			(`customer_id`, `token_id`, `type`, `card_name`, `card_number`, `card_expire_month`, `card_expire_year`, `date_added`)
			VALUES (" . (int)$data['customer_id'] . ",
			'" . $this->db->escape($data['token_id']) . "',
			'" . $this->db->escape($data['type']) . "',
			'" . $this->db->escape($data['card_name']) . "',
			'" . $this->db->escape($data['card_number']) . "',
			'" . $this->db->escape($data['card_expire_month']) . "',
			'" . $this->db->escape($data['card_expire_year']) . "',
			NOW())");
	}

	/**
	 * @param int $customer_id
	 *
	 * @return array
	 */
	public function getTokens(int $customer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_token`
			WHERE `customer_id` = " . (int)$customer_id . "
			ORDER BY `date_added` DESC");

		return $query->rows;
	}

	/**
	 * @param int $customer_id
	 * @param int $chip_token_id
	 *
	 * @return ?array
	 */
	public function getToken(int $customer_id, int $chip_token_id): ?array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_token`
			WHERE `customer_id` = " . (int)$customer_id . "
			AND `chip_token_id` = " . (int)$chip_token_id);

		if ($query->num_rows) {
			return $query->row;
		}

		return null;
	}

	/**
	 * @param int $customer_id
	 * @param int $chip_token_id
	 *
	 * @return void
	 */
	public function deleteToken(int $customer_id, int $chip_token_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "chip_token`
			WHERE `customer_id` = " . (int)$customer_id . "
			AND `chip_token_id` = " . (int)$chip_token_id);
	}

	/**
	 * Add a subscription row.
	 *
	 * The row is created `pending` with no recurring token, so it is never
	 * charged until the payment that produced the token has actually paid.
	 *
	 * @param array $data
	 *
	 * @return int The new chip_subscription_id.
	 */
	public function addSubscription(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "chip_subscription`
			SET `order_id` = " . (int)$data['order_id'] . ",
			`order_recurring_id` = " . (int)$data['order_recurring_id'] . ",
			`customer_id` = " . (int)$data['customer_id'] . ",
			`customer_email` = '" . $this->db->escape($data['customer_email']) . "',
			`chip_token_id` = " . (int)$data['chip_token_id'] . ",
			`recurring_token` = '" . $this->db->escape($data['recurring_token']) . "',
			`product_name` = '" . $this->db->escape($data['product_name']) . "',
			`product_quantity` = " . (int)$data['product_quantity'] . ",
			`recurring_frequency` = '" . $this->db->escape($data['recurring_frequency']) . "',
			`recurring_cycle` = " . (int)$data['recurring_cycle'] . ",
			`recurring_duration` = " . (int)$data['recurring_duration'] . ",
			`recurring_price` = '" . (float)$data['recurring_price'] . "',
			`trial_price` = '" . (float)$data['trial_price'] . "',
			`trial_cycle` = " . (int)$data['trial_cycle'] . ",
			`trial_frequency` = '" . $this->db->escape($data['trial_frequency']) . "',
			`trial_duration` = " . (int)$data['trial_duration'] . ",
			`remaining` = " . (int)$data['remaining'] . ",
			`trial_remaining` = " . (int)$data['trial_remaining'] . ",
			`status` = '" . $this->db->escape($data['status']) . "',
			`date_next` = '" . $this->db->escape($data['date_next']) . "',
			`date_last_charge` = '" . $this->db->escape($data['date_last_charge']) . "',
			`retry_count` = " . (int)$data['retry_count'] . ",
			`date_added` = NOW(),
			`date_modified` = NOW()");

		return $this->db->getLastId();
	}

	/**
	 * @param int $chip_subscription_id
	 *
	 * @return ?array
	 */
	public function getSubscription(int $chip_subscription_id): ?array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_subscription`
			WHERE `chip_subscription_id` = " . (int)$chip_subscription_id);

		if ($query->num_rows) {
			return $query->row;
		}

		return null;
	}

	/**
	 * @param int $order_id
	 *
	 * @return ?array
	 */
	public function getSubscriptionByOrderId(int $order_id): ?array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_subscription`
			WHERE `order_id` = " . (int)$order_id . "
			ORDER BY `chip_subscription_id` DESC LIMIT 1");

		if ($query->num_rows) {
			return $query->row;
		}

		return null;
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function getSubscriptionsByOrderId(int $order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_subscription`
			WHERE `order_id` = " . (int)$order_id . "
			ORDER BY `chip_subscription_id` ASC");

		return $query->rows;
	}

	/**
	 * Attach a recurring token and make the subscription chargeable.
	 *
	 * Only `active` rows are charged by the cron, so this is the single point
	 * where a plan becomes live.
	 *
	 * @param int    $chip_subscription_id
	 * @param string $recurring_token
	 * @param int    $chip_token_id
	 *
	 * @return void
	 */
	public function activateSubscription(int $chip_subscription_id, string $recurring_token, int $chip_token_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "chip_subscription`
			SET `recurring_token` = '" . $this->db->escape($recurring_token) . "',
			`chip_token_id` = " . (int)$chip_token_id . ",
			`status` = 'active',
			`date_last_charge` = NOW(),
			`date_modified` = NOW()
			WHERE `chip_subscription_id` = " . (int)$chip_subscription_id);
	}

	/**
	 * Subscriptions due to be charged.
	 *
	 * Only `active` rows are returned: `suspended` means the dunning ladder was
	 * exhausted and a human has to intervene, so the cron must leave them alone.
	 *
	 * @param string $date_now Cut-off (Y-m-d H:i:s).
	 * @param int    $limit
	 *
	 * @return array
	 */
	public function getDueSubscriptions(string $date_now, int $limit = 10): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_subscription`
			WHERE `status` = 'active'
			AND `date_next` != '0000-00-00 00:00:00'
			AND `date_next` <= '" . $this->db->escape($date_now) . "'
			ORDER BY `date_next` ASC
			LIMIT " . (int)$limit);

		return $query->rows;
	}

	/**
	 * Advance the schedule BEFORE the charge is attempted.
	 *
	 * The claim-first ordering is deliberate: a crash after this call costs one
	 * billing cycle, recoverable by hand. A crash before it would re-charge a
	 * real customer. When in doubt, under-charge.
	 *
	 * @param int    $chip_subscription_id
	 * @param string $date_next
	 *
	 * @return void
	 */
	public function claimSubscription(int $chip_subscription_id, string $date_next): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "chip_subscription`
			SET `date_next` = '" . $this->db->escape($date_next) . "',
			`date_modified` = NOW()
			WHERE `chip_subscription_id` = " . (int)$chip_subscription_id);
	}

	/**
	 * Record a successful renewal.
	 *
	 * @param int $chip_subscription_id
	 * @param int $remaining
	 * @param int $trial_remaining
	 *
	 * @return void
	 */
	public function recordSubscriptionPayment(int $chip_subscription_id, int $remaining, int $trial_remaining = 0): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "chip_subscription`
			SET `date_last_charge` = NOW(),
			`remaining` = " . (int)$remaining . ",
			`trial_remaining` = " . (int)$trial_remaining . ",
			`retry_count` = 0,
			`date_modified` = NOW()
			WHERE `chip_subscription_id` = " . (int)$chip_subscription_id);
	}

	/**
	 * Record a failed attempt: store the next retry time and bump the counter.
	 *
	 * @param int    $chip_subscription_id
	 * @param string $date_next
	 * @param int    $retry_count
	 * @param string $status
	 *
	 * @return void
	 */
	public function recordSubscriptionFailure(int $chip_subscription_id, string $date_next, int $retry_count, string $status = 'active'): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "chip_subscription`
			SET `date_next` = '" . $this->db->escape($date_next) . "',
			`retry_count` = " . (int)$retry_count . ",
			`status` = '" . $this->db->escape($status) . "',
			`date_modified` = NOW()
			WHERE `chip_subscription_id` = " . (int)$chip_subscription_id);
	}

	/**
	 * The next retry time for a failed charge, or null when the ladder is spent.
	 *
	 * Offsets are measured from the ORIGINAL due date so a slow cron cannot
	 * stretch the ladder.
	 *
	 * @param string $due_date    Original due date (Y-m-d H:i:s).
	 * @param int    $retry_count Failures so far, 0-based.
	 *
	 * @return ?string
	 */
	public function nextRetryAt(string $due_date, int $retry_count): ?string {
		if ($retry_count >= count(self::RETRY_OFFSETS_DAYS)) {
			return null;
		}

		$offset = self::RETRY_OFFSETS_DAYS[$retry_count];
		$timestamp = strtotime($due_date);

		if ($timestamp === false) {
			return null;
		}

		return date('Y-m-d H:i:s', strtotime('+' . $offset . ' day', $timestamp));
	}

	/**
	 * Advance a due date by one billing cycle.
	 *
	 * Anchored to the previous due date rather than "now", so a subscription
	 * billed on the 1st stays on the 1st even when the cron runs late.
	 *
	 * @param string $from_date Base date (Y-m-d H:i:s).
	 * @param string $frequency one of day/week/semi_month/month/year.
	 * @param int    $cycle
	 *
	 * @return ?string
	 */
	public function nextCycleDate(string $from_date, string $frequency, int $cycle): ?string {
		$cycle = max(1, (int)$cycle);
		$timestamp = strtotime($from_date);

		if ($timestamp === false) {
			return null;
		}

		switch ($frequency) {
			case 'day':
				$interval = '+' . $cycle . ' day';
				break;
			case 'week':
				$interval = '+' . ($cycle * 7) . ' day';
				break;
			case 'semi_month':
				$interval = '+' . ($cycle * 15) . ' day';
				break;
			case 'month':
				$interval = '+' . $cycle . ' month';
				break;
			case 'year':
				$interval = '+' . $cycle . ' year';
				break;
			default:
				return null;
		}

		return date('Y-m-d H:i:s', strtotime($interval, $timestamp));
	}

	/**
	 * Charge a renewal against a stored recurring token.
	 *
	 * @param string $purchase_id Purchase to charge (a freshly created one).
	 * @param string $token_id    The customer's recurring token.
	 *
	 * @return ?array
	 */
	public function chargeRecurring(string $purchase_id, string $token_id): ?array {
		return $this->call('POST', "/purchases/{$purchase_id}/charge/", [
			'recurring_token' => $token_id
		]);
	}

	/**
	 * Delete a recurring token at the gateway.
	 *
	 * @param string $purchase_id The purchase that issued the token.
	 *
	 * @return ?array
	 */
	public function deleteRecurringToken(string $purchase_id): ?array {
		return $this->call('POST', "/purchases/{$purchase_id}/delete_recurring_token/");
	}

	/**
	 * Whether the current cart contains a subscription product.
	 *
	 * OpenCart 4.x keeps subscription plans on the cart line itself, so this
	 * reads the same `subscription` key core's own Cart::hasSubscription()
	 * uses rather than a bespoke query.
	 *
	 * @return bool
	 */
	public function cartHasSubscription(): bool {
		return $this->cart->hasSubscription();
	}

	/**
	 * Extra purchase params required to obtain a recurring token.
	 *
	 * Returns an empty array for a normal cart. This must stay gated: an
	 * unconditional `force_recurring` would tokenise one-time payments too and
	 * change behaviour for every existing merchant.
	 *
	 * @return array
	 */
	public function recurringPurchaseParams(): array {
		if (!$this->cartHasSubscription()) {
			return [];
		}

		return [
			'force_recurring'          => true,
			'payment_method_whitelist' => self::RECURRING_CARD_METHODS
		];
	}

	/**
	 * Find the chip_token row the given purchase produced.
	 *
	 * @param string $purchase_id
	 *
	 * @return int
	 */
	public function findTokenIdByPurchase(string $purchase_id): int {
		$query = $this->db->query("SELECT `chip_token_id` FROM `" . DB_PREFIX . "chip_token`
			WHERE `token_id` = '" . $this->db->escape($purchase_id) . "' LIMIT 1");

		if ($query->num_rows) {
			return (int)$query->row['chip_token_id'];
		}

		return 0;
	}

	/**
	 * @param string $method
	 * @param string $route
	 * @param array  $params
	 *
	 * @return mixed
	 */
	private function call($method, $route, $params = []) {
		$private_key = $this->private_key;

		if (!empty($params)) {
			$params = json_encode($params);
		}

		$response = $this->request(
			$method,
			sprintf("%s/api/v1%s", 'https://gate.chip-in.asia', $route),
			$params,
			[
				'Content-type: application/json',
				'Authorization: ' . "Bearer " . $private_key
			]
		);

		$result = json_decode($response, true);

		if (!$result) {
			return null;
		}

		if (!empty($result['errors'])) {
			return null;
		}

		return $result;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param string $params
	 * @param array  $headers
	 *
	 * @return string
	 */
	private function request($method, $url, $params = [], $headers = []) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
		}

		if ($method == 'PUT') {
			curl_setopt($ch, CURLOPT_PUT, 1);
		}

		if ($method == 'PUT' or $method == 'POST') {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;
	}
}
