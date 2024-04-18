<?php
class ControllerPaymentChip extends Controller {
  private $error = array();

  public function index() {
    $this->language->load( 'payment/chip' );

    $this->document->setTitle( $this->language->get( 'heading_title' ) );

    $this->load->model('setting/setting');
    $this->load->model('localisation/order_status');
    $this->load->model('localisation/geo_zone');
    $this->load->model('localisation/language');

    if ( ( $this->request->server['REQUEST_METHOD'] == 'POST' ) && $this->validate() ) {
      unset($this->request->post['chip_module']);

      $this->model_setting_setting->editSetting('chip', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
    } else {
      $this->data['error'] = @$this->error;
    }

    $this->data['heading_title'] = $this->language->get('heading_title');

    $this->data['text_image_manager'] = $this->language->get('text_image_manager');

    $this->data['text_title'] = $this->language->get('text_title');
    $this->data['text_enabled'] = $this->language->get('text_enabled');
    $this->data['text_disabled'] = $this->language->get('text_disabled');
    $this->data['text_all_zones'] = $this->language->get('text_all_zones');
    $this->data['text_yes'] = $this->language->get('text_yes');
    $this->data['text_no'] = $this->language->get('text_no');
    $this->data['text_clear'] = $this->language->get('text_clear');
    $this->data['text_browse'] = $this->language->get('text_browse');

    $this->data['tab_general'] = $this->language->get('tab_general');
    $this->data['tab_order_status'] = $this->language->get('tab_order_status');
    $this->data['tab_api_details'] = $this->language->get('tab_api_details');
    $this->data['tab_customise'] = $this->language->get('tab_customise');
    $this->data['tab_troubleshooting'] = $this->language->get('tab_troubleshooting');

    $this->data['entry_payment_name'] = $this->language->get('entry_payment_name');
    $this->data['entry_secret_key'] = $this->language->get('entry_secret_key');
    $this->data['entry_brand_id'] = $this->language->get('entry_brand_id');
    $this->data['entry_webhook_url'] = $this->language->get('entry_webhook_url');
    $this->data['entry_public_key'] = $this->language->get('entry_public_key');
    $this->data['entry_general_public_key'] = $this->language->get('entry_general_public_key');
    $this->data['entry_purchase_send_receipt'] = $this->language->get('entry_purchase_send_receipt');
    $this->data['entry_due_strict'] = $this->language->get('entry_due_strict');
    $this->data['entry_due_strict_timing'] = $this->language->get('entry_due_strict_timing');
    $this->data['entry_time_zone'] = $this->language->get('entry_time_zone');
    $this->data['entry_debug'] = $this->language->get('entry_debug');
    $this->data['entry_convert_to_processing'] = $this->language->get('entry_convert_to_processing');
    $this->data['entry_disable_success_redirect'] = $this->language->get('entry_disable_success_redirect');
    $this->data['entry_disable_success_callback'] = $this->language->get('entry_disable_success_callback');

    $this->data['entry_total'] = $this->language->get('entry_total');
    $this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
    $this->data['entry_status'] = $this->language->get('entry_status');
    $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
    $this->data['entry_canceled_behavior'] = $this->language->get('entry_canceled_behavior');
    $this->data['entry_failed_behavior'] = $this->language->get('entry_failed_behavior');
    $this->data['entry_canceled_order_status'] = $this->language->get('entry_canceled_order_status');
    $this->data['entry_failed_order_status'] = $this->language->get('entry_failed_order_status');

    $this->data['entry_paid_order_status'] = $this->language->get('entry_paid_order_status');
    $this->data['entry_refunded_order_status'] = $this->language->get('entry_refunded_order_status');

    $this->data['entry_allow_instruction'] = $this->language->get('entry_allow_instruction');
    $this->data['entry_instruction'] = $this->language->get('entry_instruction');

    $this->data['button_save'] = $this->language->get('button_save');
    $this->data['button_cancel'] = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $this->data['error_warning'] = $this->error['warning'];
    } else {
      $this->data['error_warning'] = '';
    }

    $languages = $this->model_localisation_language->getLanguages();

    foreach ($languages as $language) {
      if (isset($this->error['instruction_' . $language['language_id']])) {
        $this->data['error_instruction_' . $language['language_id']] = $this->error['instruction_' . $language['language_id']];
      } else {
        $this->data['error_instruction_' . $language['language_id']] = '';
      }

      if (isset($this->error['payment_name_' . $language['language_id']])) {
				$this->data['error_payment_name_' . $language['language_id']] = $this->error['payment_name_' . $language['language_id']];
			} else {
				$this->data['error_payment_name_' . $language['language_id']] = '';
			}
    }

    if (isset($this->error['secret_key'])) {
      $this->data['error_secret_key'] = $this->error['secret_key'];
    } else {
      $this->data['error_secret_key'] = '';
    }

    if (isset($this->error['brand_id'])) {
      $this->data['error_brand_id'] = $this->error['brand_id'];
    } else {
      $this->data['error_brand_id'] = '';
    }

    if (isset($this->error['public_key'])) {
      $this->data['error_public_key'] = $this->error['public_key'];
    } else {
      $this->data['error_public_key'] = '';
    }

    if (isset($this->error['due_strict_timing'])) {
      $this->data['error_due_strict_timing'] = $this->error['due_strict_timing'];
    } else {
      $this->data['error_due_strict_timing'] = '';
    }

    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_home'),
      'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => false
    );

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_payment'),
      'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('heading_title'),
      'href'      => $this->url->link('payment/chip', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->data['action'] = $this->url->link('payment/chip', 'token=' . $this->session->data['token'], 'SSL');

    $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

    if (isset($this->request->post['chip_secret_key'])) {
      $this->data['chip_secret_key'] = $this->request->post['chip_secret_key'];
    } else {
      $this->data['chip_secret_key'] = $this->config->get('chip_secret_key');
    }

    if (isset($this->request->post['chip_brand_id'])) {
      $this->data['chip_brand_id'] = $this->request->post['chip_brand_id'];
    } else {
      $this->data['chip_brand_id'] = $this->config->get('chip_brand_id');
    }

    if (isset($this->request->post['chip_public_key'])) {
      $this->data['chip_public_key'] = $this->request->post['chip_public_key'];
    } else {
      $this->data['chip_public_key'] = $this->config->get('chip_public_key');
    }

    if (isset($this->request->post['chip_general_public_key'])) {
      $this->data['chip_general_public_key'] = $this->request->post['chip_general_public_key'];
    } else {
      $this->data['chip_general_public_key'] = $this->config->get('chip_general_public_key');
    }

    if (isset($this->request->post['chip_purchase_send_receipt'])) {
      $this->data['chip_purchase_send_receipt'] = $this->request->post['chip_purchase_send_receipt'];
    } else {
      $this->data['chip_purchase_send_receipt'] = $this->config->get('chip_purchase_send_receipt');
    }

    if (isset($this->request->post['chip_due_strict'])) {
      $this->data['chip_due_strict'] = $this->request->post['chip_due_strict'];
    } else {
      $this->data['chip_due_strict'] = $this->config->get('chip_due_strict');
    }

    if (isset($this->request->post['chip_due_strict_timing'])) {
      $this->data['chip_due_strict_timing'] = $this->request->post['chip_due_strict_timing'];
    } else {
      $this->data['chip_due_strict_timing'] = !empty($this->config->get('chip_due_strict_timing')) ? $this->config->get('chip_due_strict_timing') : '60';
    }

    if (isset($this->request->post['chip_total'])) {
      $this->data['chip_total'] = $this->request->post['chip_total'];
    } else {
      $this->data['chip_total'] = $this->config->get('chip_total'); 
    }

    if (isset($this->request->post['chip_canceled_order_status_id'])) {
      $this->data['chip_canceled_order_status_id'] = $this->request->post['chip_canceled_order_status_id'];
    } else {
      $this->data['chip_canceled_order_status_id'] = $this->config->get('chip_canceled_order_status_id');
    }

    if (isset($this->request->post['chip_failed_order_status_id'])) {
      $this->data['chip_failed_order_status_id'] = $this->request->post['chip_failed_order_status_id'];
    } else {
      $this->data['chip_failed_order_status_id'] = $this->config->get('chip_failed_order_status_id');
    }

    if (isset($this->request->post['chip_paid_order_status_id'])) {
      $this->data['chip_paid_order_status_id'] = $this->request->post['chip_paid_order_status_id'];
    } else {
      $this->data['chip_paid_order_status_id'] = $this->config->get('chip_paid_order_status_id');
    }

    if (isset($this->request->post['chip_refunded_order_status_id'])) {
      $this->data['chip_refunded_order_status_id'] = $this->request->post['chip_refunded_order_status_id'];
    } else {
      $this->data['chip_refunded_order_status_id'] = $this->config->get('chip_refunded_order_status_id');
    }

    if (isset($this->request->post['chip_allow_instruction'])) {
      $this->data['chip_allow_instruction'] = $this->request->post['chip_allow_instruction'];
    } else {
      $this->data['chip_allow_instruction'] = $this->config->get('chip_allow_instruction');
    }

    if (isset($this->request->post['chip_convert_to_processing'])) {
      $this->data['chip_convert_to_processing'] = $this->request->post['chip_convert_to_processing'];
    } else {
      $this->data['chip_convert_to_processing'] = $this->config->get('chip_convert_to_processing');
    }

    if (isset($this->request->post['chip_disable_success_redirect'])) {
      $this->data['chip_disable_success_redirect'] = $this->request->post['chip_disable_success_redirect'];
    } else {
      $this->data['chip_disable_success_redirect'] = $this->config->get('chip_disable_success_redirect');
    }

    if (isset($this->request->post['chip_disable_success_callback'])) {
      $this->data['chip_disable_success_callback'] = $this->request->post['chip_disable_success_callback'];
    } else {
      $this->data['chip_disable_success_callback'] = $this->config->get('chip_disable_success_callback');
    }

    if (isset($this->request->post['chip_canceled_behavior'])) {
      $this->data['chip_canceled_behavior'] = $this->request->post['chip_canceled_behavior'];
    } else {
      $this->data['chip_canceled_behavior'] = $this->config->get('chip_canceled_behavior');
    }

    if (isset($this->request->post['chip_failed_behavior'])) {
      $this->data['chip_failed_behavior'] = $this->request->post['chip_failed_behavior'];
    } else {
      $this->data['chip_failed_behavior'] = $this->config->get('chip_failed_behavior');
    }

    if (isset($this->request->post['chip_debug'])) {
      $this->data['chip_debug'] = $this->request->post['chip_debug'];
    } else {
      $this->data['chip_debug'] = $this->config->get('chip_debug');
    }

    $modified_time_zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    if (($key = array_search('Asia/Kuala_Lumpur', $modified_time_zones)) !== false) {
      unset($modified_time_zones[$key]);
      array_unshift($modified_time_zones, 'Asia/Kuala_Lumpur');
    }

    $this->data['time_zones'] = $modified_time_zones;

    if (isset($this->request->post['chip_time_zone'])) {
      $this->data['chip_time_zone'] = $this->request->post['chip_time_zone'];
    } else {
      $this->data['chip_time_zone'] = $this->config->get('chip_time_zone');
    }

    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    foreach($this->data['order_statuses'] as $order_status){
      if ($order_status['order_status_id'] == $this->config->get('config_complete_status_id')) {
        $this->data['config_complete_status_name'] = $order_status['name'];
        break;
      }
    }

    if (isset($this->request->post['chip_geo_zone_id'])) {
      $this->data['chip_geo_zone_id'] = $this->request->post['chip_geo_zone_id'];
    } else {
      $this->data['chip_geo_zone_id'] = $this->config->get('chip_geo_zone_id'); 
    }

    foreach ($languages as $language) {
      if (isset($this->request->post['chip_instruction_'. $language['language_id']])) {
        $this->data['chip_instruction_' . $language['language_id']] = $this->request->post['chip_instruction_' . $language['language_id']];
      } else {
        $this->data['chip_instruction_' . $language['language_id']] = $this->config->get('chip_instruction_' . $language['language_id']);
      }

      if (isset($this->request->post['chip_payment_name_'. $language['language_id']])) {
        $this->data['chip_payment_name_' . $language['language_id']] = $this->request->post['chip_payment_name_' . $language['language_id']];
      } else {
        $this->data['chip_payment_name_' . $language['language_id']] = $this->config->get('chip_payment_name_' . $language['language_id']);
      }
    }

    $this->data['languages'] = $languages;

    $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['chip_status'])) {
      $this->data['chip_status'] = $this->request->post['chip_status'];
    } else {
      $this->data['chip_status'] = $this->config->get('chip_status');
    }

    if (isset($this->request->post['chip_sort_order'])) {
      $this->data['chip_sort_order'] = $this->request->post['chip_sort_order'];
    } else {
      $this->data['chip_sort_order'] = $this->config->get('chip_sort_order');
    }

    $this->data['canceled_behaviors'] = array(
      'missing_order' => $this->language->get('behavior_missing_order'),
      'cancel_order' => $this->language->get('behavior_cancel_order'),
    );

    $this->data['failed_behaviors'] = array(
      'missing_order' => $this->language->get('behavior_missing_order'),
      'fail_order' => $this->language->get('behavior_fail_order'),
    );

    $this->data['webhook'] = HTTPS_CATALOG . 'index.php?route=payment/chip/callback';

    if (isset($this->request->post['chip_payment_method_whitelist'])) {
      $this->data['chip_payment_method_whitelist'] = $this->request->post['chip_payment_method_whitelist'];
    } elseif ($this->config->get('chip_payment_method_whitelist')) {
      $this->data['chip_payment_method_whitelist'] = $this->config->get('chip_payment_method_whitelist');
    } else {
      $this->data['chip_payment_method_whitelist'] = array();
    }

    $this->data['chip_available_payment_methods'] = ['fpx', 'fpx_b2b1', 'mastercard', 'maestro', 'visa', 'razer', 'razer_atome', 'razer_grabpay', 'razer_maybankqr', 'razer_shopeepay', 'razer_tng', 'duitnow_qr'];

    if (isset($this->request->post['chip_atome_minimum'])) {
      $this->data['chip_atome_minimum'] = $this->request->post['chip_atome_minimum'];
    } else {
      $this->data['chip_atome_minimum'] = $this->config->get('chip_atome_minimum');
    }

    if (isset($this->request->post['chip_atome_product_whitelist'])) {
      $this->data['chip_atome_product_whitelist'] = $this->request->post['chip_atome_product_whitelist'];
    } else {
      $this->data['chip_atome_product_whitelist'] = $this->config->get('chip_atome_product_whitelist');
    }

    $this->data['token'] = $this->session->data['token'];

    $this->template = 'payment/chip.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->response->setOutput($this->render());
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'payment/chip')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    $this->load->model('localisation/language');

    $languages = $this->model_localisation_language->getLanguages();

    foreach ($languages as $language) {
      if (isset($this->request->post['chip_allow_instruction']) && $this->request->post['chip_allow_instruction'] == '1' && !$this->request->post['chip_instruction_'. $language['language_id']]) {
        $this->error['instruction_' . $language['language_id']] = $this->language->get('error_instruction');
      }

      if (!$this->request->post['chip_payment_name_'. $language['language_id']]) {
        $this->error['payment_name_' . $language['language_id']] = $this->language->get('error_payment_name');
      }
    }

    if ($this->request->post['chip_secret_key']) {
      $this->configure_general_public_key();
    } else {
      $this->error['secret_key'] = $this->language->get('error_secret_key');
    }

    if (!$this->request->post['chip_due_strict_timing']) {
      $this->error['due_strict_timing'] = $this->language->get('error_due_strict_timing');
    }

    if (!$this->request->post['chip_brand_id']) {
      $this->error['brand_id'] = $this->language->get('error_brand_id');
    }

    if ($this->request->post['chip_public_key']) {
      $public_key_validity = openssl_pkey_get_public($this->request->post['chip_public_key']);

      if (!$public_key_validity) {
        $this->error['public_key'] = $this->language->get('error_public_key');
      }
    }

    if (!$this->error) {
      return true;
    } else {
      return false;
    }
  }

  public function install() {
    $this->load->model('payment/chip');
    $this->model_payment_chip->install();
  }

  public function uninstall() {
    $this->load->model('payment/chip');
    $this->model_payment_chip->uninstall();
  }

  private function configure_general_public_key() {
    $this->load->model('payment/chip');
    $this->model_payment_chip->set_keys($this->request->post['chip_secret_key'], '');
    $general_public_key = str_replace('\n', "\n", $this->model_payment_chip->get_public_key());

    if (isset($general_public_key['__all__'])) {
      $this->error['secret_key'] = implode('. ', $general_public_key['__all__'][0]);
      return false;
    }

    if (empty($general_public_key) OR !openssl_pkey_get_public($general_public_key)){
      $this->error['secret_key'] = $this->language->get('error_secret_key_invalid');
      return false;
    }

    $this->request->post['chip_general_public_key'] = $general_public_key;
    return true;
  }
}