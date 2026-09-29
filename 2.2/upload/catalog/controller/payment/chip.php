<?php
// Version reported to the gateway. Keep in step with install.json.
if (!defined('CHIP_OPENCART_VERSION')) {
	define('CHIP_OPENCART_VERSION', '1.2.0');
}
class ControllerPaymentChip extends Controller {
	public function index() {
		$this->language->load('payment/chip');

		$data['text_instruction'] = $this->language->get('text_instruction');

		$data['chip_allow_instruction'] = $this->config->get('chip_allow_instruction');
		$data['chip_instruction'] = nl2br($this->config->get('chip_instruction_' . $this->config->get('config_language_id')));

		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_continue_action'] = $this->url->link('payment/chip/create_purchase', '', true);

		// Stored cards for logged-in customers
		$data['chip_tokens'] = array();

		if ($this->customer->isLogged()) {
			$this->load->model('payment/chip');

			$tokens = $this->model_payment_chip->getTokens($this->customer->getId());

			foreach ($tokens as $token) {
				$data['chip_tokens'][] = array(
					'chip_token_id' => $token['chip_token_id'],
					'name'          => $this->language->get('text_card_use') . ' ' . $this->language->get('text_' . $token['type']) . ' ' . $token['card_number'],
					'href'          => $this->url->link('payment/chip/create_purchase_stored', 'chip_token_id=' . $token['chip_token_id'], true)
				);
			}
		}

		/**
			* if there is any other chip session data, clear it
			*/
		unset($this->session->data['chip']);

		return $this->load->view('payment/chip', $data);
	}

	public function create_purchase_stored() {
		if ($this->session->data['payment_method']['code'] != 'chip') {
			exit;
		}

		$this->language->load('payment/chip');

		if (!isset($this->request->get['chip_token_id'])) {
			$this->session->data['error'] = $this->language->get('error_invalid_token');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$chip_token_id = (int)$this->request->get['chip_token_id'];

		$this->load->model('payment/chip');
		$this->load->model('checkout/order');
		$this->load->model('account/order');

		$token_data = $this->model_payment_chip->getTokenByChipTokenId($chip_token_id);

		if (!$token_data || !isset($token_data['token_id'])) {
			$this->session->data['error'] = $this->language->get('error_invalid_token');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);

		/* Reject if MYR currency is not set up */

		if (!$this->currency->has('MYR')){
			$this->session->data['error'] = $this->language->get('pending_myr_setup');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$total_override = $order_info['total'];
		
		if ($this->config->get('chip_convert_to_processing') == 0 AND $this->config->get('config_currency') != 'MYR') {
			$this->session->data['error'] = $this->language->get('convert_to_processing_disabled');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		if ($this->config->get('config_currency') != 'MYR') {
			$total_override = $this->currency->convert($order_info['total'], $this->config->get('config_currency'), 'MYR');
		}

		$params = array(
			'success_callback' => $this->url->link('payment/chip/success_callback', '', true),
			'success_redirect' => $this->url->link('payment/chip/success_redirect', '', true),
			'failure_redirect' => $this->url->link('checkout/checkout', '', true),
			'cancel_redirect'  => $this->url->link('checkout/cart', '', true),
			'creator_agent'    => 'OC22: ' . CHIP_OPENCART_VERSION,
			'reference'        => $this->session->data['order_id'],
			'platform'         => 'opencart',
			'due'              => time() + (abs( (int) $this->config->get('chip_due_strict_timing') ) * 60),
			'brand_id'         => $this->config->get('chip_brand_id'),
			'client'           => [],
			'purchase'         => array(
				'total_override' => round($total_override * 100),
				'timezone'       => $this->config->get('chip_time_zone'),
				'currency'       => 'MYR',
				'due_strict'     => $this->config->get('chip_due_strict'),
				'products'       => array(),
			),
		);

		$payment_method_whitelist = $this->config->get('chip_payment_method_whitelist');
		if (is_array($payment_method_whitelist) AND sizeof($payment_method_whitelist) > 0) {
			$params['payment_method_whitelist'] = $this->model_payment_chip->resolve_payment_method_whitelist(
				$payment_method_whitelist,
				'MYR',
				$params['purchase']['total_override']
			);
		}

		if ($this->config->get('chip_disable_success_redirect')) {
			unset($params['success_redirect']);
		}

		if ($this->config->get('chip_disable_success_callback')) {
			unset($params['success_callback']);
		}

		if ($this->config->get('chip_canceled_behavior') == 'cancel_order') {
			$params['cancel_redirect'] = $this->url->link('payment/chip/cancel_redirect', '', true);
		}

		if ($this->config->get('chip_failed_behavior') == 'fail_order') {
			$params['failure_redirect'] = $this->url->link('payment/chip/failure_redirect', '', true);
		}

		foreach ($products as $product) {
			$product_price = $this->currency->convert($product['price'], $this->config->get('config_currency'), 'MYR');

			$params['purchase']['products'][] = array(
				'name' => substr($product['name'], 0, 256),
				'quantity' => $product['quantity'],
				'price' => round($product_price * 100),
				'category' => $product['product_id']
			);
		}

		if (!empty($order_info['comment'])) {
			$params['purchase']['notes'] = substr($order_info['comment'], 0, 10000);
		}

		if (!empty($order_info['email'])) {
			$params['client']['email'] = $order_info['email'];
		}

		if (!empty($order_info['telephone'])) {
			$params['client']['phone'] = $order_info['telephone'];
		}

		$params_client_full_name = array();
		if ($order_info['payment_firstname']) {
			$params_client_full_name[] = $order_info['payment_firstname'];
		}

		if ($order_info['payment_lastname']) {
			$params_client_full_name[] = ' ' . $order_info['payment_lastname'];
		}

		if (!empty(trim(implode($params_client_full_name)))){
			$params['client']['full_name'] = substr(implode($params_client_full_name), 0, 30);
		}

		/* Start of payment information */

		$params_client_street_address = array();
		if (!empty($order_info['payment_address_1'])) {
			$params_client_street_address[] = $order_info['payment_address_1'];
		}

		if (!empty($order_info['payment_address_2'])) {
			$params_client_street_address[] = $order_info['payment_address_2'];
		}

		if (!empty($params_client_street_address)){
			$params['client']['street_address'] = substr(implode($params_client_street_address), 0, 128);
		}

		if (!empty($order_info['payment_postcode'])) {
			$params['client']['zip_code'] = substr($order_info['payment_postcode'], 0, 32);
		}

		if (!empty($order_info['payment_city'])) {
			$params['client']['city'] = substr($order_info['payment_city'], 0, 128);
		}

		if (!empty($order_info['payment_iso_code_2'])) {
			$params['client']['country'] = $order_info['payment_iso_code_2'];
		}

		/* End of payment information */
		/* Start of shipping information */

		$params_client_shipping_street_address = array();
		if (!empty($order_info['shipping_address_1'])) {
			$params_client_shipping_street_address[] = $order_info['shipping_address_1'];
		}

		if (!empty($order_info['shipping_address_2'])) {
			$params_client_shipping_street_address[] = ' ' . $order_info['shipping_address_2'];
		}

		if (!empty($params_client_shipping_street_address)) {
			$params['client']['shipping_street_address'] = substr(implode($params_client_shipping_street_address), 0, 128);
		}

		if (!empty($order_info['shipping_postcode'])) {
			$params['client']['shipping_zip_code'] = substr($order_info['shipping_postcode'], 0, 32);
		}

		if (!empty($order_info['shipping_city'])) {
			$params['client']['shipping_city'] = substr($order_info['shipping_city'], 0, 128);
		}

		if (!empty($order_info['shipping_iso_code_2'])) {
			$params['client']['shipping_country'] = $order_info['shipping_iso_code_2'];
		}

		/* End of shipping information */

		$this->model_payment_chip->set_keys($this->config->get('chip_secret_key'), '');

		$purchase = $this->model_payment_chip->create_purchase($params);

		if ( !is_array($purchase) || !array_key_exists('id', $purchase) ) {
			$this->session->data['error'] = print_r($purchase, true);

			if ($this->config->get('chip_debug')) {
				$this->log->write('CHIP API /purchase/ failed for order #' . $this->session->data['order_id'] . '. Response Body: ' . json_encode($purchase));
			}

			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$this->session->data['chip'] = $purchase;

		// Save to chip_report table
		$customer_id = $order_info['customer_id'];
		$chip_id = $purchase['id'];
		$order_id = $order_info['order_id'];
		$status = isset($purchase['status']) ? $purchase['status'] : 'pending';
		$amount = $params['purchase']['total_override'] / 100;
		$environment_type = isset($purchase['is_test']) && $purchase['is_test'] ? 'staging' : 'production';

		$this->model_payment_chip->addReport(array(
			'customer_id' => $customer_id,
			'chip_id' => $chip_id,
			'order_id' => $order_id,
			'status' => $status,
			'amount' => $amount,
			'environment_type' => $environment_type
		));

		// Charge the stored token
		$this->model_payment_chip->chargeToken($chip_id, $token_data['token_id']);

		$this->response->redirect($purchase['checkout_url']);
	}

	public function create_purchase() {
		if ($this->session->data['payment_method']['code'] != 'chip') {
			exit;
		}

		$this->load->model('payment/chip');
		$this->load->model('checkout/order');
		$this->load->model('account/order');

		$this->language->load('payment/chip');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);

		/* Reject if MYR currency is not set up */

		if (!$this->currency->has('MYR')){
			$this->session->data['error'] = $this->language->get('pending_myr_setup');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$total_override = $order_info['total'];
		
		if ($this->config->get('chip_convert_to_processing') == 0 AND $this->config->get('config_currency') != 'MYR') {
			$this->session->data['error'] = $this->language->get('convert_to_processing_disabled');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		if ($this->config->get('config_currency') != 'MYR') {
			$total_override = $this->currency->convert($order_info['total'], $this->config->get('config_currency'), 'MYR');
		}

		$params = array(
			'success_callback' => $this->url->link('payment/chip/success_callback', '', true),
			'success_redirect' => $this->url->link('payment/chip/success_redirect', '', true),
			'failure_redirect' => $this->url->link('checkout/checkout', '', true),
			'cancel_redirect'  => $this->url->link('checkout/cart', '', true),
			'creator_agent'    => 'OC22: ' . CHIP_OPENCART_VERSION,
			'reference'        => $this->session->data['order_id'],
			'platform'         => 'opencart',
			'due'              => time() + (abs( (int) $this->config->get('chip_due_strict_timing') ) * 60),
			'brand_id'         => $this->config->get('chip_brand_id'),
			'client'           => [],
			'purchase'         => array(
				'total_override' => round($total_override * 100),
				'timezone'       => $this->config->get('chip_time_zone'),
				'currency'       => 'MYR',
				'due_strict'     => $this->config->get('chip_due_strict'),
				'products'       => array(),
			),
		);

		$payment_method_whitelist = $this->config->get('chip_payment_method_whitelist');
		if (is_array($payment_method_whitelist) AND sizeof($payment_method_whitelist) > 0) {
			$params['payment_method_whitelist'] = $this->model_payment_chip->resolve_payment_method_whitelist(
				$payment_method_whitelist,
				'MYR',
				$params['purchase']['total_override']
			);
		}

		/*
		 * Recurring products: ask CHIP for a recurring token, card only.
		 *
		 * Deliberately overrides the merchant whitelist above - CHIP issues
		 * recurring tokens for card payments only, and leaving a non-card
		 * method in the list would let the customer pick a method that cannot
		 * be charged on a cycle later.
		 */
		if ($this->model_payment_chip->cartHasRecurring()) {
			$params['force_recurring']          = true;
			$params['payment_method_whitelist'] = ModelPaymentChip::RECURRING_CARD_METHODS;
		}

		if ($this->config->get('chip_disable_success_redirect')) {
			unset($params['success_redirect']);
		}

		if ($this->config->get('chip_disable_success_callback')) {
			unset($params['success_callback']);
		}

		if ($this->config->get('chip_canceled_behavior') == 'cancel_order') {
			$params['cancel_redirect'] = $this->url->link('payment/chip/cancel_redirect', '', true);
		}

		if ($this->config->get('chip_failed_behavior') == 'fail_order') {
			$params['failure_redirect'] = $this->url->link('payment/chip/failure_redirect', '', true);
		}

		foreach ($products as $product) {
			$product_price = $this->currency->convert($product['price'], $this->config->get('config_currency'), 'MYR');

			$params['purchase']['products'][] = array(
				'name' => substr($product['name'], 0, 256),
				'quantity' => $product['quantity'],
				'price' => round($product_price * 100),
				'category' => $product['product_id']
			);
		}

		if (!empty($order_info['comment'])) {
			$params['purchase']['notes'] = substr($order_info['comment'], 0, 10000);
		}

		if (!empty($order_info['email'])) {
			$params['client']['email'] = $order_info['email'];
		}

		if (!empty($order_info['telephone'])) {
			$params['client']['phone'] = $order_info['telephone'];
		}

		$params_client_full_name = array();
		if ($order_info['payment_firstname']) {
			$params_client_full_name[] = $order_info['payment_firstname'];
		}

		if ($order_info['payment_lastname']) {
			$params_client_full_name[] = ' ' . $order_info['payment_lastname'];
		}

		if (!empty(trim(implode($params_client_full_name)))){
			$params['client']['full_name'] = substr(implode($params_client_full_name), 0, 30);
		}

		/* Start of payment information */

		$params_client_street_address = array();
		if (!empty($order_info['payment_address_1'])) {
			$params_client_street_address[] = $order_info['payment_address_1'];
		}

		if (!empty($order_info['payment_address_2'])) {
			$params_client_street_address[] = $order_info['payment_address_2'];
		}

		if (!empty($params_client_street_address)){
			$params['client']['street_address'] = substr(implode($params_client_street_address), 0, 128);
		}

		if (!empty($order_info['payment_postcode'])) {
			$params['client']['zip_code'] = substr($order_info['payment_postcode'], 0, 32);
		}

		if (!empty($order_info['payment_city'])) {
			$params['client']['city'] = substr($order_info['payment_city'], 0, 128);
		}

		if (!empty($order_info['payment_iso_code_2'])) {
			$params['client']['country'] = $order_info['payment_iso_code_2'];
		}

		/* End of payment information */
		/* Start of shipping information */

		$params_client_shipping_street_address = array();
		if (!empty($order_info['shipping_address_1'])) {
			$params_client_shipping_street_address[] = $order_info['shipping_address_1'];
		}

		if (!empty($order_info['shipping_address_2'])) {
			$params_client_shipping_street_address[] = ' ' . $order_info['shipping_address_2'];
		}

		if (!empty($params_client_shipping_street_address)) {
			$params['client']['shipping_street_address'] = substr(implode($params_client_shipping_street_address), 0, 128);
		}

		if (!empty($order_info['shipping_postcode'])) {
			$params['client']['shipping_zip_code'] = substr($order_info['shipping_postcode'], 0, 32);
		}

		if (!empty($order_info['shipping_city'])) {
			$params['client']['shipping_city'] = substr($order_info['shipping_city'], 0, 128);
		}

		if (!empty($order_info['shipping_iso_code_2'])) {
			$params['client']['shipping_country'] = $order_info['shipping_iso_code_2'];
		}

		/* End of shipping information */

		$this->model_payment_chip->set_keys($this->config->get('chip_secret_key'), '');

		$purchase = $this->model_payment_chip->create_purchase($params);

		if ( !is_array($purchase) || !array_key_exists('id', $purchase) ) {
			$this->session->data['error'] = print_r($purchase, true);

			if ($this->config->get('chip_debug')) {
				$this->log->write('CHIP API /purchase/ failed for order #' . $this->session->data['order_id'] . '. Response Body: ' . json_encode($purchase));
			}

			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$this->session->data['chip'] = $purchase;

		// Save to chip_report table
		$customer_id = $order_info['customer_id'];
		$chip_id = $purchase['id'];
		$order_id = $order_info['order_id'];
		$status = isset($purchase['status']) ? $purchase['status'] : 'pending';
		$amount = $params['purchase']['total_override'] / 100;
		$environment_type = isset($purchase['is_test']) && $purchase['is_test'] ? 'staging' : 'production';

		$this->model_payment_chip->addReport(array(
			'customer_id' => $customer_id,
			'chip_id' => $chip_id,
			'order_id' => $order_id,
			'status' => $status,
			'amount' => $amount,
			'environment_type' => $environment_type
		));

		// Persist any recurring plan before the customer leaves for CHIP.
		$this->saveRecurringPlan($this->session->data['order_id']);

		$this->response->redirect($purchase['checkout_url']);
	}

	public function success_callback() {
		$this->load->model('checkout/order');
		$this->load->model('payment/chip');
		$this->language->load('payment/chip');

		/*
		 * Normalise the stored key before use.
		 *
		 * A gateway response that arrives double-encoded leaves LITERAL
		 * backslash-n sequences in the setting, which openssl_pkey_get_public()
		 * cannot parse. The old save-side expression was `str_replace('\n',
		 * "\n", $key)`, which in PHP replaces a newline with a newline and so
		 * never repaired it - the setting silently disabled this whole check.
		 * Normalising at the point of USE also rescues merchants who already
		 * saved a malformed value.
		 */
		$public_key = str_replace(array('\\n', '\\r'), array("\n", ''), (string)$this->config->get('chip_general_public_key'));

		if (!isset($this->request->server['HTTP_X_SIGNATURE'])) {
			$this->refuseCallback('No HTTP_X_SIGNATURE detected');
		}

		$HTTP_X_SIGNATURE = $this->request->server['HTTP_X_SIGNATURE'];

		$purchase_json = file_get_contents('php://input');

		/*
		 * A missing or unusable public key must be a REFUSAL, not a PHP warning
		 * that falls through to a 200. openssl_verify() returns false on a key
		 * it cannot coerce, and the warning text was previously rendered into
		 * the response body while the status stayed 200.
		 */
		if ($public_key === '' || @openssl_pkey_get_public($public_key) === false) {
			$this->refuseCallback('Callback signature key is not usable');
		}

		if (openssl_verify( $purchase_json,  base64_decode($HTTP_X_SIGNATURE), $public_key, 'sha256WithRSAEncryption' ) != 1) {
			$this->refuseCallback('Invalid X-Signature');
		}

		$purchase = json_decode($purchase_json, true);

		if ($purchase['status'] != 'paid') {
			exit;
		}

		$purchase_id = $purchase['id'];

		$this->db->query("SELECT GET_LOCK('chip_payment_$purchase_id', 15);");

		$order_info = $this->model_checkout_order->getOrder($purchase['reference']);
		if ($order_info['order_status_id'] != $this->config->get('chip_paid_order_status_id')) {
			$this->model_checkout_order->addOrderHistory($purchase['reference'], $this->config->get('chip_paid_order_status_id'), $this->language->get('payment_successful') .' '. $purchase_id, true);
			$this->model_checkout_order->addOrderHistory($purchase['reference'], $this->config->get('chip_paid_order_status_id'), $this->language->get('payment_method') . strtoupper($purchase['transaction_data']['payment_method']));

			if ($purchase['is_test'] == true) {
				$this->model_checkout_order->addOrderHistory($purchase['reference'], $this->config->get('chip_paid_order_status_id'), $this->language->get('test_mode_disclaimer'));
			}
		}

		// Update chip_report status to paid
		$this->model_payment_chip->updateReportStatus($purchase_id, 'paid');

		// Save token if is_recurring_token is true
		if (isset($purchase['is_recurring_token']) && $purchase['is_recurring_token'] === true) {
			$this->saveToken($purchase, $order_info['customer_id']);
		}

		// Attach the recurring token so the renewal cron can charge it.
		$this->attachSubscriptionToken($purchase, $order_info);

		$this->db->query("SELECT RELEASE_LOCK('chip_payment_$purchase_id');");

		exit;
	}

	/**
	 * Refuse a callback and say so with a real HTTP status.
	 *
	 * The previous shape was
	 * `addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 401 Unauthorized')`,
	 * which builds the header "HTTP/1.1/1.1 401 Unauthorized" - malformed
	 * because SERVER_PROTOCOL is ALREADY "HTTP/1.1". PHP's header() ignores a
	 * line that does not start with a valid protocol, so a forged signature
	 * still produced HTTP 200. A webhook that answers 200 to a forged body
	 * tells the gateway the delivery succeeded.
	 *
	 * PHP 7.4's header() accepts a status LINE directly, so the status is
	 * passed on its own; the reason phrase is the response body, which is the
	 * only part a log will preserve.
	 */
	private function refuseCallback($reason) {
		if (!headers_sent()) {
			header('HTTP/1.1 401 Unauthorized', true, 401);
		}

		exit($reason);
	}

	public function success_redirect() {
		$this->language->load('payment/chip');

		if (!isset($this->session->data['chip'])) {
			exit($this->language->get('invalid_redirect'));
		}

		$purchase_id = $this->session->data['chip']['id'];
		$order_id = $this->session->data['chip']['reference'];

		$this->load->model('checkout/order');
		$this->load->model('payment/chip');

		$this->model_payment_chip->set_keys($this->config->get('chip_secret_key'), '');
		$purchase = $this->model_payment_chip->get_purchase($purchase_id);

		if ( !is_array($purchase) || !array_key_exists('id', $purchase) ) {
			$this->session->data['error'] = print_r($purchase, true);

			if ($this->config->get('chip_debug')) {
				$this->log->write('CHIP API /purchase/'.$purchase_id. '/ failed for order #' . $order_id . '. Response Body: ' . json_encode($purchase));
			}

			$this->response->redirect($this->session->data['chip']['checkout_url'] . 'receipt/');
		}

		if ($purchase['status'] != 'paid') {
			exit;
		}

		unset($this->session->data['chip']);

		$this->db->query("SELECT GET_LOCK('chip_payment_$purchase_id', 15);");

		$order_info = $this->model_checkout_order->getOrder($order_id);
		if ($order_info['order_status_id'] != $this->config->get('chip_paid_order_status_id')) {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('chip_paid_order_status_id'), $this->language->get('payment_successful') .' '. $purchase_id, true);
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('chip_paid_order_status_id'), $this->language->get('payment_method') . strtoupper($purchase['transaction_data']['payment_method']));

			if ($purchase['is_test'] == true) {
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('chip_paid_order_status_id'), $this->language->get('test_mode_disclaimer'));
			}
		}

		// Update chip_report status to paid
		$this->model_payment_chip->updateReportStatus($purchase_id, 'paid');

		// Save token if is_recurring_token is true
		if (isset($purchase['is_recurring_token']) && $purchase['is_recurring_token'] === true) {
			$this->saveToken($purchase, $order_info['customer_id']);
		}

		// Attach the recurring token so the renewal cron can charge it.
		$this->attachSubscriptionToken($purchase, $order_info);

		$this->db->query("SELECT RELEASE_LOCK('chip_payment_$purchase_id');");

		$this->response->redirect($this->url->link('checkout/success', '', true));
	}

	public function cancel_redirect() {
		$this->language->load('payment/chip');

		if (!isset($this->session->data['chip'])) {
			exit($this->language->get('invalid_redirect'));
		}

		$purchase_id = $this->session->data['chip']['id'];
		$order_id = $this->session->data['chip']['reference'];

		$this->load->model('checkout/order');
		$this->load->model('payment/chip');

		unset($this->session->data['chip']);

		$this->db->query("SELECT GET_LOCK('chip_payment_$purchase_id', 15);");

		$order_info = $this->model_checkout_order->getOrder($order_id);
		if ($order_info['order_status_id'] != $this->config->get('chip_canceled_order_status_id')) {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('chip_canceled_order_status_id'), $this->language->get('payment_canceled') .' '. $purchase_id, true);
		}

		// Update chip_report status to canceled
		$this->model_payment_chip->updateReportStatus($purchase_id, 'canceled');

		$this->db->query("SELECT RELEASE_LOCK('chip_payment_$purchase_id');");

		$this->response->redirect($this->url->link('checkout/failure', '', true));
	}

	public function failure_redirect() {
		$this->language->load('payment/chip');

		if (!isset($this->session->data['chip'])) {
			exit($this->language->get('invalid_redirect'));
		}

		$purchase_id = $this->session->data['chip']['id'];
		$order_id = $this->session->data['chip']['reference'];

		$this->load->model('checkout/order');
		$this->load->model('payment/chip');

		unset($this->session->data['chip']);

		$this->db->query("SELECT GET_LOCK('chip_payment_$purchase_id', 15);");

		$order_info = $this->model_checkout_order->getOrder($order_id);
		if ($order_info['order_status_id'] != $this->config->get('chip_failed_order_status_id')) {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('chip_failed_order_status_id'), $this->language->get('payment_failed') .' '. $purchase_id, true);
		}

		// Update chip_report status to failed
		$this->model_payment_chip->updateReportStatus($purchase_id, 'failed');

		$this->db->query("SELECT RELEASE_LOCK('chip_payment_$purchase_id');");

		$this->response->redirect($this->url->link('checkout/failure', '', true));
	}

	/**
	 * Recurring renewal cron endpoint.
	 *
	 * CHIP does not renew subscriptions, so the merchant must schedule this
	 * themselves:
	 *
	 *   index.php?route=payment/chip/cron&token=<cron_token>
	 *
	 * The token is compared with hash_equals() against the value generated at
	 * install time. This endpoint charges real cards, so an unauthenticated
	 * request must never reach the charging code.
	 */
	public function cron() {
		$expected = (string)$this->config->get('chip_cron_token');
		$provided = isset($this->request->get['token']) ? (string)$this->request->get['token'] : '';

		if ($expected === '' || !hash_equals($expected, $provided)) {
			/*
			 * `$this->response->addHeader($this->request->server['SERVER_PROTOCOL']
			 * . '/1.1 403 Forbidden')` built "HTTP/1.1/1.1 403 Forbidden" -
			 * malformed, because SERVER_PROTOCOL is ALREADY "HTTP/1.1" - and PHP
			 * ignored the line, so an unauthenticated caller was told 200 OK.
			 * The charge was still refused, but the status is what a monitor,
			 * a WAF or the gateway reads, so it must be truthful.
			 */
			if (!headers_sent()) {
				header('HTTP/1.1 403 Forbidden', true, 403);
			}

			exit('Forbidden');
		}

		$this->language->load('payment/chip');
		$this->load->model('payment/chip');
		$this->load->model('checkout/order');

		$now  = date('Y-m-d H:i:s');
		$due  = $this->model_payment_chip->getDueSubscriptions($now, 10);
		$done = 0;

		foreach ($due as $subscription) {
			$lock = 'chip_subscription_' . (int)$subscription['chip_subscription_id'];

			$acquired = $this->db->query("SELECT GET_LOCK('" . $lock . "', 5) AS acquired");

			if (!$acquired->row['acquired']) {
				// Another cron run holds this subscription. Skip, do not double-charge.
				continue;
			}

			/*
			 * Re-read the row now that the lock is held, and re-check that it
			 * is still due.
			 *
			 * The due list was read ONCE before this loop, so it is a snapshot:
			 * between that read and this lock being granted, another cron run
			 * may have already billed this row and advanced its date_next. The
			 * lock makes the loser WAIT - it does not make the loser NOTICE the
			 * work is done. Without this check the loser goes on to bill a
			 * customer whose period was just charged, which the database cannot
			 * show (each row still advances exactly one cycle) and only the
			 * gateway's charge log exposes.
			 */
			$fresh = $this->model_payment_chip->getSubscription((int)$subscription['chip_subscription_id']);

			if (!$fresh
				|| $fresh['status'] !== 'active'
				|| $fresh['date_next'] === '0000-00-00 00:00:00'
				|| $fresh['date_next'] !== $subscription['date_next']) {
				// Already billed by the run that beat us to the lock, or no
				// longer due. Release and skip rather than charge again.
				$this->db->query("SELECT RELEASE_LOCK('" . $lock . "');");
				continue;
			}

			$subscription = $fresh;

			if ($this->chargeSubscription($subscription)) {
				$done++;
			}

			$this->db->query("SELECT RELEASE_LOCK('" . $lock . "');");
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('due' => count($due), 'charged' => $done)));
	}

	/**
	 * Charge one renewal, applying the dunning ladder on failure.
	 *
	 * @param array $subscription chip_subscription row.
	 *
	 * @return bool Whether the charge succeeded.
	 */
	private function chargeSubscription($subscription) {
		/*
		 * Load ONCE and keep the model.
		 *
		 * `$this->model_payment_chip` is unusable here: on 2.2 Loader::model()
		 * registers a Proxy whose every method call re-instantiates the model
		 * through Action::execute(), so `last_error_code` is written on one
		 * throwaway object and read from another. See
		 * probe_loader_instancing.php - the same instance DOES report the code,
		 * the proxy never can.
		 *
		 * A direct instance therefore has to be constructed, and the class is
		 * not auto-loaded on this page.
		 */
		if (!class_exists('ModelPaymentChip')) {
			include_once DIR_APPLICATION . 'model/payment/chip.php';
		}

		$model_payment_chip = new ModelPaymentChip($this->registry);

		/*
		 * `date_next` may hold a retry time left by the previous attempt
		 * rather than the date this cycle was due: claimSubscription() and
		 * recordSubscriptionFailure() both rewrite the column. Recover the real
		 * due date first, so the 1/3/5 retry ladder is measured from it and the
		 * claim below advances the billing schedule from it too.
		 */
		$due_date        = $model_payment_chip->ladderAnchor($subscription['date_next'], (int)$subscription['retry_count']);
		$frequency       = $subscription['recurring_frequency'];
		$cycle           = (int)$subscription['recurring_cycle'];
		$duration        = (int)$subscription['recurring_duration'];
		$remaining       = (int)$subscription['remaining'];
		$trial_remaining = (int)$subscription['trial_remaining'];
		$retry_count     = (int)$subscription['retry_count'];

		/*
		 * A trial cycle bills the trial price, not the recurring price.
		 *
		 * Charging recurring_price while trial cycles remain would overcharge
		 * the customer for the whole trial period.
		 */
		$in_trial   = $trial_remaining > 0;
		$unit_price = $in_trial ? (float)$subscription['trial_price'] : (float)$subscription['recurring_price'];

		/*
		 * Claim first, charge second.
		 *
		 * Advancing the due date BEFORE the charge is what makes a cron that
		 * runs more than once per window safe. A crash here costs one cycle and
		 * is recoverable by hand; a crash the other way round would charge a
		 * real customer twice.
		 */
		if ($in_trial) {
			$step_frequency = (string)$subscription['trial_frequency'];
			$step_cycle     = (int)$subscription['trial_cycle'];
		} else {
			$step_frequency = $frequency;
			$step_cycle     = $cycle;
		}

		$next_cycle = $this->model_payment_chip->nextCycleDate($due_date, $step_frequency, $step_cycle);

		if ($next_cycle === null) {
			$this->model_payment_chip->recordSubscriptionFailure(
				$subscription['chip_subscription_id'], '0000-00-00 00:00:00', $retry_count, 'suspended');

			return false;
		}

		/*
		 * Fixed-duration plan with no cycles left: close it out.
		 *
		 * `remaining` counts cycles still to charge (set to `duration` on
		 * activation and decremented per successful renewal), so the terminal
		 * test is <= 0 - not <= 1, which would hand the customer a free final
		 * period by completing the plan without charging it.
		 */
		if ($duration > 0 && $remaining <= 0) {
			$this->model_payment_chip->recordSubscriptionFailure(
				$subscription['chip_subscription_id'], '0000-00-00 00:00:00', 0, 'completed');

			return true;
		}

		$this->model_payment_chip->claimSubscription($subscription['chip_subscription_id'], $next_cycle);

		// Mint a fresh purchase for the renewal amount and charge the token.
		$params = array(
			'reference'        => $subscription['order_id'],
			'platform'         => 'opencart',
			'creator_agent'    => 'OC22: ' . CHIP_OPENCART_VERSION,
			'brand_id'         => $this->config->get('chip_brand_id'),
			'client'           => array(
				'email' => $subscription['customer_email'],
			),
			'purchase'         => array(
				'timezone' => $this->config->get('chip_time_zone'),
				'currency' => 'MYR',
				'products' => array(
					array(
						'name'     => substr($subscription['product_name'], 0, 256),
						'quantity' => (int)$subscription['product_quantity'],
						'price'    => round($unit_price * 100),
					),
				),
			),
			'recurring_token'  => $subscription['recurring_token'],
		);

		$model_payment_chip->set_keys($this->config->get('chip_secret_key'), '');

		$purchase = $model_payment_chip->create_purchase($params);

		if (!is_array($purchase) || !array_key_exists('id', $purchase)) {
			return $this->failSubscription($subscription, $due_date, $retry_count, $this->language->get('error_renewal_purchase'), $model_payment_chip);
		}

		$charge = $model_payment_chip->chargeRecurring($purchase['id'], $subscription['recurring_token']);

		return $this->recordChargeOutcome($subscription, $charge, $model_payment_chip, $due_date, $retry_count);
	}

	/**
	 * Decide a charge's outcome and record it.
	 *
	 * A money-path guard must name all THREE states explicitly - paid, still
	 * settling, failed - and "paid or else failed" collapses the middle one into
	 * the last, which retries a charge that may still succeed.
	 *
	 * The decision is returned rather than stashed on $this, because PHP does
	 * not re-dispatch an `exit` inside a helper and the caller must act on the
	 * result.
	 *
	 * @param array  $subscription
	 * @param mixed  $charge       Gateway charge response (array|null).
	 * @param object $model        The instance that performed the charge.
	 * @param string $due_date
	 * @param int    $retry_count
	 *
	 * @return bool Whether the renewal succeeded.
	 */
	private function recordChargeOutcome($subscription, $charge, $model, $due_date, $retry_count) {
		/*
		 * An unresolved charge is NOT a failure.
		 *
		 * CHIP answers HTTP 200 with `status = 'pending_charge'` when the
		 * acquirer has not finalised, and follows up with a `purchase.paid`
		 * or `purchase.payment_failed` callback. Treating that as a decline
		 * walked the retry ladder and re-charged on the next step while the
		 * first charge was still settling - a double-charge window.
		 *
		 * So: do not start a second charge. Leave the billing date that
		 * claimSubscription() already advanced (this row is therefore not
		 * due again in this window), keep the retry ladder untouched since
		 * nothing failed, and do not consume a cycle.
		 */
		if (is_array($charge) && isset($charge['status']) && $charge['status'] === 'pending_charge') {
			/*
			 * Logged against the order's CURRENT status, not a paid or failed
			 * one: a pending charge has resolved to neither, and flipping the
			 * order either way would misreport it to the merchant.
			 */
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($subscription['order_id']);

			$order_status_id = isset($order_info['order_status_id']) ? (int)$order_info['order_status_id'] : 0;

			if ($order_status_id) {
				$this->model_checkout_order->addOrderHistory($subscription['order_id'], $order_status_id, $this->language->get('text_renewal_pending'), false);
			}

			return false;
		}

		if (!is_array($charge) || !isset($charge['status']) || $charge['status'] !== 'paid') {
			return $this->failSubscription($subscription, $due_date, $retry_count, $this->language->get('error_renewal_charge'), $model);
		}

		// Success.
		$in_trial        = (int)$subscription['trial_remaining'] > 0;
		$duration        = (int)$subscription['recurring_duration'];
		$remaining       = (int)$subscription['remaining'];
		$trial_remaining = (int)$subscription['trial_remaining'];

		if ($in_trial) {
			$new_trial_remaining = max(0, $trial_remaining - 1);
			$new_remaining       = $remaining;
		} else {
			$new_trial_remaining = 0;
			$new_remaining       = $duration > 0 ? max(0, $remaining - 1) : 0;
		}

		$this->model_payment_chip->recordSubscriptionPayment(
			$subscription['chip_subscription_id'], $new_remaining, $new_trial_remaining);

		$this->load->model('checkout/order');

		$this->model_checkout_order->addOrderHistory(
			$subscription['order_id'],
			$this->config->get('chip_paid_order_status_id'),
			$this->language->get('text_renewal_success') . ' ' . $charge['id'],
			true
		);

		if ($duration > 0 && !$in_trial && $new_remaining <= 0) {
			$this->model_payment_chip->recordSubscriptionFailure(
				$subscription['chip_subscription_id'], '0000-00-00 00:00:00', 0, 'completed');
		}

		return true;
	}

	/**
	 * Handle a failed renewal: step down the dunning ladder, or suspend.
	 *
	 * @param array  $subscription
	 * @param string $due_date     The due date this attempt belonged to.
	 * @param int    $retry_count
	 * @param string $reason
	 *
	 * @return bool Always false.
	 */
	private function failSubscription($subscription, $due_date, $retry_count, $reason, $model_payment_chip) {
		/*
		 * `model_payment_chip` MUST be the instance that performed the charge.
		 *
		 * Loader::model() always builds a NEW object and re-sets the registry,
		 * so calling it in here would replace the model that recorded
		 * `last_error_code` with an empty one and silently discard the code -
		 * which makes the dead-token check below dead code. It is a required
		 * parameter for that reason: a default would let it regress silently.
		 */

$next_retry = $model_payment_chip->nextRetryAt($due_date, $retry_count);

		$error_code = (string)$model_payment_chip->getLastErrorCode();

		/*
		 * A dead or revoked token can never succeed. CHIP documents
		 * `invalid_recurring_token` as "do not retry, re-prompt the buyer for a new
		 * card", so suspend now instead of spending the whole ladder on a charge
		 * that is guaranteed to fail.
		 */
		if ($error_code === 'invalid_recurring_token') {
			$model_payment_chip->recordSubscriptionFailure(
				$subscription['chip_subscription_id'], '0000-00-00 00:00:00', $retry_count + 1, 'suspended');

			$this->model_checkout_order->addOrderHistory(
				$subscription['order_id'],
				$this->config->get('chip_failed_order_status_id'),
				$this->language->get('text_renewal_token_dead'),
				true
			);

			return false;
		}


		if ($next_retry === null) {
			/*
			 * Ladder exhausted. Suspend rather than cancel: the card is kept so
			 * the merchant can recover the subscription once the customer tops
			 * up or replaces the card.
			 */
			$model_payment_chip->recordSubscriptionFailure(
				$subscription['chip_subscription_id'], '0000-00-00 00:00:00', $retry_count + 1, 'suspended');

			$this->model_checkout_order->addOrderHistory(
				$subscription['order_id'],
				$this->config->get('chip_failed_order_status_id'),
				$this->language->get('text_renewal_suspended'),
				true
			);
		} else {
			$model_payment_chip->recordSubscriptionFailure(
				$subscription['chip_subscription_id'], $next_retry, $retry_count + 1, 'active');

			$this->model_checkout_order->addOrderHistory(
				$subscription['order_id'],
				$this->config->get('chip_failed_order_status_id'),
				$this->language->get('text_renewal_failed') . ' ' . $reason . ' - ' . $this->language->get('text_renewal_retry') . ' ' . $next_retry,
				false
			);
		}

		return false;
	}

	private function saveToken($purchase, $customer_id) {
		if (!isset($purchase['transaction_data']['extra'])) {
			return;
		}

		$extra = $purchase['transaction_data']['extra'];

		// Check if required fields exist
		if (!isset($extra['card_type']) || !isset($extra['masked_pan']) ||
				!isset($extra['expiry_month']) || !isset($extra['expiry_year'])) {
			return;
		}

		$token_data = array(
			'customer_id' => $customer_id,
			'token_id' => $purchase['id'],
			'type' => $extra['card_brand'],
			'card_name' => isset($extra['cardholder_name']) ? $extra['cardholder_name'] : '',
			'card_number' => $extra['masked_pan'],
			'card_expire_month' => $extra['expiry_month'],
			'card_expire_year' => $extra['expiry_year']
		);

		$this->model_payment_chip->addToken($token_data);
	}
	/**
	 * Record the recurring plans in the cart before the customer is sent to CHIP.
	 *
	 * The cart is gone by the time the callback fires, so the plan has to be
	 * persisted here. The row starts as `pending` and only becomes `active`
	 * once a recurring token exists - a pending row is never charged.
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	private function saveRecurringPlan($order_id) {
		$this->load->model('payment/chip');

		if (!$this->model_payment_chip->cartHasRecurring()) {
			return;
		}

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if (!$order_info) {
			return;
		}

		foreach ($this->cart->getProducts() as $product) {
			if (empty($product['recurring'])) {
				continue;
			}

			$recurring = $product['recurring'];
			$duration  = (int)$recurring['duration'];
			$trial     = (int)$recurring['trial'];
			$trial_len = (int)$recurring['trial_duration'];

			/*
			 * The first cycle follows the trial schedule when the plan has a
			 * trial, otherwise the normal recurring schedule.
			 */
			if ($trial === 1 && $trial_len > 0) {
				$first_frequency = $recurring['trial_frequency'];
				$first_cycle     = (int)$recurring['trial_cycle'];
			} else {
				$first_frequency = $recurring['frequency'];
				$first_cycle     = (int)$recurring['cycle'];
			}

			$next = $this->model_payment_chip->nextCycleDate(
				date('Y-m-d H:i:s'),
				$first_frequency,
				$first_cycle
			);

			if ($next === null) {
				continue;
			}

			$this->model_payment_chip->addSubscription(array(
				'order_id'            => (int)$order_id,
				'order_recurring_id'  => 0,
				'customer_id'         => (int)$order_info['customer_id'],
				'customer_email'      => (string)$order_info['email'],
				'chip_token_id'       => 0,
				'recurring_token'     => '',
				'product_name'        => (string)$product['name'],
				'product_quantity'    => (int)$product['quantity'],
				'recurring_frequency' => (string)$recurring['frequency'],
				'recurring_cycle'     => (int)$recurring['cycle'],
				'recurring_duration'  => $duration,
				'recurring_price'     => (float)$recurring['price'],
				'trial_price'         => (float)$recurring['trial_price'],
				'trial_cycle'         => (int)$recurring['trial_cycle'],
				'trial_frequency'     => (string)$recurring['trial_frequency'],
				'trial_duration'      => (int)$recurring['trial_duration'],
				'remaining'           => $duration,
				'trial_remaining'     => ($trial === 1 ? $trial_len : 0),
				'status'              => 'pending',
				'date_next'           => $next,
				'date_last_charge'    => '0000-00-00 00:00:00',
				'retry_count'         => 0,
			));
		}
	}

	/**
	 * The next charge date for a subscription that is being re-armed.
	 *
	 * Mirrors the cron's own step choice: while trial cycles remain the plan
	 * advances by the trial schedule, otherwise by the recurring one. Computing
	 * this anywhere else would let the two drift, and a plan that re-arms onto
	 * the wrong cadence bills the customer on a schedule they never agreed to.
	 *
	 * @param array $subscription chip_subscription row.
	 *
	 * @return string Date, or '' when the schedule is unusable.
	 */
	private function rearmDate($subscription) {
		if ((int)$subscription['trial_remaining'] > 0) {
			$frequency = (string)$subscription['trial_frequency'];
			$cycle     = (int)$subscription['trial_cycle'];
		} else {
			$frequency = (string)$subscription['recurring_frequency'];
			$cycle     = (int)$subscription['recurring_cycle'];
		}

		$model = $this->model_payment_chip;

		$next = $model->nextCycleDate(date('Y-m-d H:i:s'), $frequency, $cycle);

		return ($next === null) ? '' : $next;
	}

	/**
	 * Attach the recurring token once a recurring purchase is paid.
	 *
	 * The token is the purchase id. Until this runs the subscription stays
	 * `pending` and the cron ignores it, so a plan that never completed payment
	 * can never be charged.
	 *
	 * @param array $purchase   Paid CHIP purchase.
	 * @param array $order_info Order row.
	 *
	 * @return void
	 */
	private function attachSubscriptionToken($purchase, $order_info) {
		if (!isset($purchase['is_recurring_token']) || $purchase['is_recurring_token'] !== true) {
			return;
		}

		$this->load->model('payment/chip');

		$subscriptions = $this->model_payment_chip->getSubscriptionsByOrderId($order_info['order_id']);

		if (!$subscriptions) {
			return;
		}

		$chip_token_id = $this->findTokenIdByPurchase($purchase['id']);

		foreach ($subscriptions as $subscription) {
			/*
			 * Re-arm a suspended row in the same call.
			 *
			 * Suspension zeroes date_next and leaves the retry ladder spent, so
			 * activating without restoring the schedule leaves a row that
			 * getDueSubscriptions() can never select again - the subscription
			 * reads as active and is never billed.
			 *
			 * A `pending` row is a plan that has never been paid; its schedule
			 * was just written by the checkout, so it is left alone.
			 */
			$rearm = ($subscription['status'] === 'suspended')
				? $this->rearmDate($subscription)
				: '';

			$this->model_payment_chip->activateSubscription(
				$subscription['chip_subscription_id'],
				(string)$purchase['id'],
				$chip_token_id,
				$rearm
			);
		}
	}

	/**
	 * Find the chip_token row the given purchase produced.
	 *
	 * @param string $purchase_id
	 *
	 * @return int
	 */
	private function findTokenIdByPurchase($purchase_id) {
		$query = $this->db->query("SELECT `chip_token_id` FROM `" . DB_PREFIX . "chip_token`
			WHERE `token_id` = '" . $this->db->escape($purchase_id) . "' LIMIT 1");

		if ($query->num_rows) {
			return (int)$query->row['chip_token_id'];
		}

		return 0;
	}
}
