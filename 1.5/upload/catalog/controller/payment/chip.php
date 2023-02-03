<?php
class ControllerPaymentChip extends Controller
{
  protected function index() {
    $this->language->load('payment/chip');

    $this->data['text_instruction'] = $this->language->get('text_instruction');

    $this->data['chip'] = nl2br($this->config->get('chip_' . $this->config->get('config_language_id')));

    $this->data['button_continue'] = $this->language->get('button_continue');
    $this->data['button_continue_action'] = $this->url->link('payment/chip/create_purchase', '', 'SSL');

    /**
     * if there is any other chip session data, clear it
     */
    unset($this->session->data['chip']);

    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/chip.tpl')) {
      $this->template = $this->config->get('config_template') . '/template/payment/chip.tpl';
    } else {
      $this->template = 'default/template/payment/chip.tpl';
    }

    $this->render();
  }

  public function create_purchase() {
    if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
      $this->redirect($this->url->link('checkout/cart'));
    }

    $this->load->model('payment/chip');
    $this->load->model('checkout/order');

    $this->language->load('payment/chip');

    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

    /* Reject if MYR currency is not set up */

    if (!$this->currency->has('MYR')){
      $this->session->data['error'] = $this->language->get('pending_myr_setup');
      $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
    }

    $total_override = $this->currency->convert($order_info['total'], $this->config->get('config_currency'), 'MYR');

    if ($this->config->get('chip_convert_to_processing') == 0 AND $this->config->get('config_currency') != 'MYR') {
      $this->session->data['error'] = $this->language->get('convert_to_processing_disabled');
      $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
    }

    $params = array(
      'success_callback' => $this->url->link('payment/chip/success_callback', '', 'SSL'),
      // 'success_redirect' => $this->url->link('payment/chip/success_redirect', '', 'SSL'),
      // 'failure_redirect' => $this->url->link('payment/chip/failure_redirect', '', 'SSL'),
      // 'cancel_redirect'  => $this->url->link('checkout/checkout', '', 'SSL'),
      'creator_agent'    => 'OC15: 1.0.0',
      'reference'        => $this->session->data['order_id'],
      'platform'         => 'opencart',
      'send_receipt'     => $this->config->get('chip_purchase_send_receipt'),
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

    $products = $this->cart->getProducts();
    $subtotal_override = $this->cart->getSubTotal();

    $params['purchase']['subtotal_override'] = round($subtotal_override * 100);

    foreach ($products as $product) {
      $product_price = $this->currency->convert($product['price'], $this->config->get('config_currency'), 'MYR');

      $params['purchase']['products'][] = array(
        'name' => substr($product['name'], 0, 256),
        'quantity' => $product['quantity'],
        'price' => round($product_price * 100),
        'category' => $product['product_id']
      );
    }

    if ($order_info['email']) {
      $params['client']['email'] = $order_info['email'];
    }

    if ($order_info['telephone']) {
      $params['client']['phone'] = $order_info['telephone'];
    }

    $params_client_full_name = array();
    if ($order_info['payment_firstname']) {
      $params_client_full_name[] = $order_info['payment_firstname'];
    }

    if ($order_info['payment_lastname']) {
      $params_client_full_name[] = ' ' . $order_info['payment_lastname'];
    }

    $params['client']['full_name'] = substr(implode($params_client_full_name), 0, 30);

    /* Start of payment information */

    $params_client_street_address = array();
    if ($order_info['payment_address_1']) {
      $params_client_street_address[] = $order_info['payment_address_1'];
    }

    if ($order_info['payment_address_2']) {
      $params_client_street_address[] = $order_info['payment_address_2'];
    }

    $params['client']['street_address'] = substr(implode($params_client_street_address), 0, 128);

    if ($order_info['payment_postcode']) {
      $params['client']['zip_code'] = substr($order_info['payment_postcode'], 0, 32);
    }

    if ($order_info['payment_city']) {
      $params['client']['city'] = substr($order_info['payment_city'], 0, 128);
    }

    if ($order_info['payment_iso_code_2']) {
      $params['client']['country'] = $order_info['payment_iso_code_2'];
    }

    /* End of payment information */
    /* Start of shipping information */

    $params_client_shipping_street_address = array();
    if ($order_info['shipping_address_1']) {
      $params_client_shipping_street_address[] = $order_info['shipping_address_1'];
    }

    if ($order_info['shipping_address_2']) {
      $params_client_shipping_street_address[] = $order_info['shipping_address_2'];
    }

    $params['client']['shipping_street_address'] = substr(implode($params_client_shipping_street_address), 0, 128);

    if ($order_info['shipping_postcode']) {
      $params['client']['shipping_zip_code'] = substr($order_info['shipping_postcode'], 0, 32);
    }

    if ($order_info['shipping_city']) {
      $params['client']['shipping_city'] = substr($order_info['shipping_city'], 0, 128);
    }

    if ($order_info['shipping_iso_code_2']) {
      $params['client']['shipping_country'] = $order_info['shipping_iso_code_2'];
    }

    /* End of shipping information */

    $this->model_payment_chip->set_keys($this->config->get('chip_secret_key'), '');

    $purchase = $this->model_payment_chip->create_purchase($params);

    if ( !array_key_exists('id', $purchase) ) {
      $this->session->data['error'] = print_r($purchase, true);

      if ($this->config->get('chip_debug')) {
        $this->log->write('CHIP API /purchase/ failed for order #' . $this->session->data['order_id'] . '. Response Body: ' . json_encode($purchase));
      }

      $this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
    }

    $this->session->data['chip'] = $purchase;

    header('Location: ' . $purchase['checkout_url']);
  }

  public function callback() {
    $this->load->model('payment/chip');

    $public_key = $this->config->get('chip_public_key');
    /* yet to be completed */
  }
  public function success_callback() {
    $this->load->model('checkout/order');

    $public_key = $this->config->get('chip_general_public_key');

    if (!isset($this->request->server['HTTP_X_SIGNATURE'])) {
      exit('No HTTP_X_SIGNATURE detected');
    }

    $HTTP_X_SIGNATURE = $this->request->server['HTTP_X_SIGNATURE'];

    $purchase_json = file_get_contents('php://input');

    if (openssl_verify( $purchase_json,  base64_decode($HTTP_X_SIGNATURE), $public_key, 'sha256WithRSAEncryption' ) != 1) {
      header("HTTP/1.1 401 Unauthorized");
      exit;
    }

    $purchase = json_decode($purchase_json, true);

    if ($purchase['status'] != 'paid') {
      exit;
    }

    $purchase_id = $purchase['id'];

    $this->db->query("SELECT GET_LOCK('chip_payment_$purchase_id', 15);");

    $order_info = $this->model_checkout_order->getOrder($purchase['reference']);
    if ($order_info['order_status_id'] != $this->config->get('chip_paid_order_status_id')) {
      $this->model_checkout_order->confirm($purchase['reference'], $this->config->get('chip_paid_order_status_id'), sprintf('Payment Successful. CHIP receipt: https://gate.chip-in.asia/p/%s/receipt/', $purchase_id), true);

      if ($purchase['is_test'] == true) {
        $this->model_checkout_order->update($purchase['reference'], $this->config->get('chip_paid_order_status_id'), 'The payment made in test mode where it does not involve real payment.');
      }
    }

    $this->db->query("SELECT RELEASE_LOCK('chip_payment_$purchase_id');");

    exit;
  }

  /* for redirect, must call $this->cart->clear(); */
}