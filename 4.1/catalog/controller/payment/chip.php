<?php
namespace Opencart\Catalog\Controller\Extension\Chip\Payment;
class Chip extends \Opencart\System\Engine\Controller
{
  public function index(): string {
    $this->load->language('extension/chip/payment/chip');

    $data['payment_chip_allow_instruction'] = $this->config->get('payment_chip_allow_instruction');
    $data['payment_chip_instruction'] = nl2br($this->config->get('payment_chip_instruction_' . $this->config->get('config_language_id')));

    /**
     * if there is any other chip session data, clear it
     */
    unset($this->session->data['chip']);

    return $this->load->view('extension/chip/payment/chip', $data);
  }

  public function create_purchase() {
    $this->load->language('extension/chip/payment/chip');

    $json = [];

    if (!isset($this->session->data['order_id'])) {
      $json['error'] = $this->language->get('error_order_id');
      $this->response->addHeader('Content-Type: application/json');
		  $this->response->setOutput(json_encode($json));
      return;
    }

    if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != 'chip') {
      $json['error'] = $this->language->get('error_payment_method');
      $this->response->addHeader('Content-Type: application/json');
		  $this->response->setOutput(json_encode($json));
      return;
    }

    $this->load->model('extension/chip/payment/chip');
    $this->load->model('checkout/order');

    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    $products = $this->model_checkout_order->getProducts($this->session->data['order_id']);

    /* Reject if MYR currency is not set up */

    if (!$this->currency->has('MYR')){
      $json['error'] = $this->language->get('pending_myr_setup');
      $this->response->addHeader('Content-Type: application/json');
		  $this->response->setOutput(json_encode($json));
      return;
    }

    $total_override = $order_info['total'];
    
    if ($this->config->get('payment_chip_convert_to_processing') == 0 AND $this->config->get('config_currency') != 'MYR') {
      $json['error'] = $this->language->get('convert_to_processing_disabled');
      $this->response->addHeader('Content-Type: application/json');
		  $this->response->setOutput(json_encode($json));
      return;
    }

    if ($this->config->get('config_currency') != 'MYR') {
      $total_override = $this->currency->convert($order_info['total'], $this->config->get('config_currency'), 'MYR');
    }

    $params = array(
      'success_callback' => $this->url->link('extension/chip/payment/chip|success_callback'),
      'success_redirect' => $this->url->link('extension/chip/payment/chip|success_redirect'),
      'failure_redirect' => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language')),
      'cancel_redirect'  => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')),
      'creator_agent'    => 'OC40: 1.0.0',
      'reference'        => $this->session->data['order_id'],
      'platform'         => 'opencart',
      'due'              => time() + (abs( (int) $this->config->get('payment_chip_due_strict_timing') ) * 60),
      'brand_id'         => $this->config->get('payment_chip_brand_id'),
      'client'           => [],
      'purchase'         => array(
        'total_override' => round($total_override * 100),
        'timezone'       => $this->config->get('payment_chip_time_zone'),
        'currency'       => 'MYR',
        'due_strict'     => $this->config->get('payment_chip_due_strict'),
        'products'       => array(),
      ),
    );

    if ($this->config->get('payment_chip_disable_success_redirect')) {
      unset($params['success_redirect']);
    }

    if ($this->config->get('payment_chip_disable_success_callback')) {
      unset($params['success_callback']);
    }

    if ($this->config->get('payment_chip_canceled_behavior') == 'cancel_order') {
      $params['cancel_redirect'] = $this->url->link('extension/chip/payment/chip|cancel_redirect');
    }

    if ($this->config->get('payment_chip_failed_behavior') == 'fail_order') {
      $params['failure_redirect'] = $this->url->link('extension/chip/payment/chip|failure_redirect');
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

    $this->model_extension_chip_payment_chip->set_keys($this->config->get('payment_chip_secret_key'), 'brand-id');

    if ($this->customer->isLogged()) {
      $client_with_params = $params['client'];
      unset($params['client']);

      $get_client = $this->model_extension_chip_payment_chip->get_client_by_email($this->customer->getEmail());

      if (array_key_exists('__all__', $get_client)) {
        $json['error'] = print_r('Invalid Secret Key', true);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        return;
      }

      if (is_array($get_client['results']) AND !empty($get_client['results'])) {
        $client = $get_client['results'][0];
      } else {
        $client = $this->model_extension_chip_payment_chip->create_client($client_with_params);
      }

      $params['client_id'] = $client['id'];
    }

    $purchase = $this->model_extension_chip_payment_chip->create_purchase($params);

    if ( !array_key_exists('id', $purchase) ) {
      $json['error'] = print_r($purchase, true);

      if ($this->config->get('payment_chip_debug')) {
        $this->log->write('CHIP API /purchase/ failed for order #' . $this->session->data['order_id'] . '. Response Body: ' . json_encode($purchase));
      }

      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($json));
      return;
    }

    $this->session->data['chip'] = $purchase;

    $json['redirect'] = $purchase['checkout_url'];

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function success_callback() {
    $this->load->model('checkout/order');
    $this->language->load('extension/chip/payment/chip');

    $public_key = $this->config->get('payment_chip_general_public_key');

    if (!isset($this->request->server['HTTP_X_SIGNATURE'])) {
      exit('No HTTP_X_SIGNATURE detected');
    }

    $HTTP_X_SIGNATURE = $this->request->server['HTTP_X_SIGNATURE'];

    $purchase_json = file_get_contents('php://input');

    if (openssl_verify( $purchase_json,  base64_decode($HTTP_X_SIGNATURE), $public_key, 'sha256WithRSAEncryption' ) != 1) {
      $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 401 Unauthorized');
      exit;
    }

    $purchase = json_decode($purchase_json, true);

    if ($purchase['status'] != 'paid') {
      exit;
    }

    $purchase_id = $purchase['id'];

    $this->db->query("SELECT GET_LOCK('payment_chip_payment_$purchase_id', 15);");

    $order_info = $this->model_checkout_order->getOrder($purchase['reference']);
    if ($order_info['order_status_id'] != $this->config->get('payment_chip_paid_order_status_id')) {
      $this->model_checkout_order->addHistory($purchase['reference'], $this->config->get('payment_chip_paid_order_status_id'), $this->language->get('payment_successful') .' '. sprintf($this->language->get('chip_receipt_url'), $purchase_id), true);
      $this->model_checkout_order->addHistory($purchase['reference'], $this->config->get('payment_chip_paid_order_status_id'), $this->language->get('payment_method') . strtoupper($purchase['transaction_data']['payment_method']));

      if ($purchase['is_test'] == true) {
        $this->model_checkout_order->addHistory($purchase['reference'], $this->config->get('payment_chip_paid_order_status_id'), $this->language->get('test_mode_disclaimer'));
      }
    }

    $this->db->query("SELECT RELEASE_LOCK('payment_chip_payment_$purchase_id');");

    exit;
  }

  public function success_redirect() {
    $this->language->load('extension/chip/payment/chip');

    if (!isset($this->session->data['chip'])) {
      exit($this->language->get('invalid_redirect'));
    }

    $purchase_id = $this->session->data['chip']['id'];
    $order_id = $this->session->data['chip']['reference'];

    $this->load->model('checkout/order');
    $this->load->model('extension/chip/payment/chip');

    $this->model_extension_chip_payment_chip->set_keys($this->config->get('payment_chip_secret_key'), '');
    $purchase = $this->model_extension_chip_payment_chip->get_purchase($purchase_id);

    if ( !array_key_exists('id', $purchase) ) {
      $json['error'] = print_r($purchase, true);

      if ($this->config->get('payment_chip_debug')) {
        $this->log->write('CHIP API /purchase/'.$purchase_id. '/ failed for order #' . $order_id . '. Response Body: ' . json_encode($purchase));
      }

      $this->response->redirect($this->session->data['chip']['checkout_url'] . 'receipt/');
    }

    if ($purchase['status'] != 'paid') {
      exit;
    }

    unset($this->session->data['chip']);

    $this->db->query("SELECT GET_LOCK('payment_chip_payment_$purchase_id', 15);");

    $order_info = $this->model_checkout_order->getOrder($order_id);
    if ($order_info['order_status_id'] != $this->config->get('payment_chip_paid_order_status_id')) {
      $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_chip_paid_order_status_id'), $this->language->get('payment_successful') .' '. sprintf($this->language->get('chip_receipt_url'), $purchase_id), true);
      $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_chip_paid_order_status_id'), $this->language->get('payment_method') . strtoupper($purchase['transaction_data']['payment_method']));

      if ($purchase['is_test'] == true) {
        $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_chip_paid_order_status_id'), $this->language->get('test_mode_disclaimer'));
      }
    }

    $this->db->query("SELECT RELEASE_LOCK('payment_chip_payment_$purchase_id');");

    $this->response->redirect($this->url->link('checkout/success'));
  }

  public function cancel_redirect() {
    $this->load->language('extension/chip/payment/chip');

    if (!isset($this->session->data['chip'])) {
      exit($this->language->get('invalid_redirect'));
    }

    $purchase_id = $this->session->data['chip']['id'];
    $order_id = $this->session->data['chip']['reference'];

    $this->load->model('checkout/order');

    unset($this->session->data['chip']);

    $this->db->query("SELECT GET_LOCK('payment_chip_payment_$purchase_id', 15);");

    $order_info = $this->model_checkout_order->getOrder($order_id);
    if ($order_info['order_status_id'] != $this->config->get('payment_chip_canceled_order_status_id')) {
      $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_chip_canceled_order_status_id'), $this->language->get('payment_canceled') .' '. sprintf($this->language->get('chip_invoice_url'), $purchase_id), true);
    }

    $this->db->query("SELECT RELEASE_LOCK('payment_chip_payment_$purchase_id');");

    $this->response->redirect($this->url->link('checkout/failure'));
  }

  public function failure_redirect() {
    $this->load->language('extension/chip/payment/chip');

    if (!isset($this->session->data['chip'])) {
      exit($this->language->get('invalid_redirect'));
    }

    $purchase_id = $this->session->data['chip']['id'];
    $order_id = $this->session->data['chip']['reference'];

    $this->load->model('checkout/order');

    unset($this->session->data['chip']);

    $this->db->query("SELECT GET_LOCK('payment_chip_payment_$purchase_id', 15);");

    $order_info = $this->model_checkout_order->getOrder($order_id);
    if ($order_info['order_status_id'] != $this->config->get('payment_chip_failed_order_status_id')) {
      $this->model_checkout_order->addHistory($order_id, $this->config->get('payment_chip_failed_order_status_id'), $this->language->get('payment_failed') .' '. sprintf($this->language->get('chip_invoice_url'), $purchase_id), true);
    }

    $this->db->query("SELECT RELEASE_LOCK('payment_chip_payment_$purchase_id');");

    $this->response->redirect($this->url->link('checkout/failure'));
  }
}