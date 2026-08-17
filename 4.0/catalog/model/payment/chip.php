<?php
namespace Opencart\Catalog\Model\Extension\Chip\Payment;

class Chip extends \Opencart\System\Engine\Model {
	const DUITNOW_GROUP = ['duitnow_qr', 'dnqr'];
	const SHOPEE_GROUP = ['razer_shopeepay', 'shopee_pay'];

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

		if ($this->cart->hasSubscription()) {
			$status = false;
		} elseif (!$this->config->get('payment_chip_geo_zone_id')) {
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

		if ($this->cart->hasSubscription()) {
			$status = false;
		} elseif (!$this->config->get('payment_chip_geo_zone_id')) {
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
