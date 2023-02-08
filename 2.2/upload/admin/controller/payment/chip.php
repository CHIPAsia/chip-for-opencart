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
      $this->model_setting_setting->editSetting('chip', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], true));
    } else {
      $data['error'] = @$this->error;
    }

    $data['heading_title'] = $this->language->get('heading_title');

    $data['text_title'] = $this->language->get('text_title');
    $data['text_edit'] = $this->language->get('text_edit');
    $data['text_enabled'] = $this->language->get('text_enabled');
    $data['text_disabled'] = $this->language->get('text_disabled');
    $data['text_all_zones'] = $this->language->get('text_all_zones');
    $data['text_yes'] = $this->language->get('text_yes');
    $data['text_no'] = $this->language->get('text_no');
    $data['text_clear'] = $this->language->get('text_clear');
    $data['text_browse'] = $this->language->get('text_browse');

    $data['tab_general'] = $this->language->get('tab_general');
    $data['tab_order_status'] = $this->language->get('tab_order_status');
    $data['tab_api_details'] = $this->language->get('tab_api_details');
    $data['tab_checkout'] = $this->language->get('tab_checkout');
    $data['tab_troubleshoot'] = $this->language->get('tab_troubleshoot');

    $data['entry_payment_name'] = $this->language->get('entry_payment_name');
    $data['entry_secret_key'] = $this->language->get('entry_secret_key');
    $data['entry_brand_id'] = $this->language->get('entry_brand_id');
    $data['entry_webhook_url'] = $this->language->get('entry_webhook_url');
    $data['entry_public_key'] = $this->language->get('entry_public_key');
    $data['entry_general_public_key'] = $this->language->get('entry_general_public_key');
    $data['entry_purchase_send_receipt'] = $this->language->get('entry_purchase_send_receipt');
    $data['entry_due_strict'] = $this->language->get('entry_due_strict');
    $data['entry_due_strict_timing'] = $this->language->get('entry_due_strict_timing');
    $data['entry_time_zone'] = $this->language->get('entry_time_zone');
    $data['entry_debug'] = $this->language->get('entry_debug');
    $data['entry_convert_to_processing'] = $this->language->get('entry_convert_to_processing');
    $data['entry_disable_success_redirect'] = $this->language->get('entry_disable_success_redirect');
    $data['entry_disable_success_callback'] = $this->language->get('entry_disable_success_callback');

    $data['entry_total'] = $this->language->get('entry_total');
    $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
    $data['entry_status'] = $this->language->get('entry_status');
    $data['entry_sort_order'] = $this->language->get('entry_sort_order');

    $data['entry_paid_order_status'] = $this->language->get('entry_paid_order_status');
    $data['entry_refunded_order_status'] = $this->language->get('entry_refunded_order_status');

    $data['entry_allow_instruction'] = $this->language->get('entry_allow_instruction');
    $data['entry_instruction'] = $this->language->get('entry_instruction');

    $data['help_payment_name'] = $this->language->get('help_payment_name');
    $data['help_secret_key'] = $this->language->get('help_secret_key');
    $data['help_brand_id'] = $this->language->get('help_brand_id');
    $data['help_webhook_url'] = $this->language->get('help_webhook_url');
    $data['help_public_key'] = $this->language->get('help_public_key');
    $data['help_general_public_key'] = $this->language->get('help_general_public_key');
    $data['help_due_strict'] = $this->language->get('help_due_strict');
    $data['help_due_strict_timing'] = $this->language->get('help_due_strict_timing');
    $data['help_total'] = $this->language->get('help_total');
    $data['help_status'] = $this->language->get('help_status');
    $data['help_sort_order'] = $this->language->get('help_sort_order');
    $data['help_paid_order_status'] = $this->language->get('help_paid_order_status');
    $data['help_refunded_order_status'] = $this->language->get('help_refunded_order_status');
    $data['help_allow_instruction'] = $this->language->get('help_allow_instruction');
    $data['help_instruction'] = $this->language->get('help_instruction');
    $data['help_time_zone'] = $this->language->get('help_time_zone');
    $data['help_convert_to_processing'] = $this->language->get('help_convert_to_processing');
    $data['help_disable_success_redirect'] = $this->language->get('help_disable_success_redirect');
    $data['help_disable_success_callback'] = $this->language->get('help_disable_success_callback');

    $data['button_save'] = $this->language->get('button_save');
    $data['button_cancel'] = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    $languages = $this->model_localisation_language->getLanguages();

    foreach ($languages as $language) {
      if (isset($this->error['instruction_' . $language['language_id']])) {
        $data['error_instruction_' . $language['language_id']] = $this->error['instruction_' . $language['language_id']];
      } else {
        $data['error_instruction_' . $language['language_id']] = '';
      }

      if (isset($this->error['payment_name_' . $language['language_id']])) {
				$data['error_payment_name_' . $language['language_id']] = $this->error['payment_name_' . $language['language_id']];
			} else {
				$data['error_payment_name_' . $language['language_id']] = '';
			}
    }

    if (isset($this->error['secret_key'])) {
      $data['error_secret_key'] = $this->error['secret_key'];
    } else {
      $data['error_secret_key'] = '';
    }

    if (isset($this->error['brand_id'])) {
      $data['error_brand_id'] = $this->error['brand_id'];
    } else {
      $data['error_brand_id'] = '';
    }

    if (isset($this->error['public_key'])) {
      $data['error_public_key'] = $this->error['public_key'];
    } else {
      $data['error_public_key'] = '';
    }

    if (isset($this->error['due_strict_timing'])) {
      $data['error_due_strict_timing'] = $this->error['due_strict_timing'];
    } else {
      $data['error_due_strict_timing'] = '';
    }

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_home'),
      'href'      => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
    );

    $data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_payment'),
      'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true),
    );

    $data['breadcrumbs'][] = array(
      'text'      => $this->language->get('heading_title'),
      'href'      => $this->url->link('payment/chip', 'token=' . $this->session->data['token'], true),
    );

    $data['action'] = $this->url->link('payment/chip', 'token=' . $this->session->data['token'], true);

    $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true);

    if (isset($this->request->post['chip_secret_key'])) {
      $data['chip_secret_key'] = $this->request->post['chip_secret_key'];
    } else {
      $data['chip_secret_key'] = $this->config->get('chip_secret_key');
    }

    if (isset($this->request->post['chip_brand_id'])) {
      $data['chip_brand_id'] = $this->request->post['chip_brand_id'];
    } else {
      $data['chip_brand_id'] = $this->config->get('chip_brand_id');
    }

    if (isset($this->request->post['chip_public_key'])) {
      $data['chip_public_key'] = $this->request->post['chip_public_key'];
    } else {
      $data['chip_public_key'] = $this->config->get('chip_public_key');
    }

    if (isset($this->request->post['chip_general_public_key'])) {
      $data['chip_general_public_key'] = $this->request->post['chip_general_public_key'];
    } else {
      $data['chip_general_public_key'] = $this->config->get('chip_general_public_key');
    }

    if (isset($this->request->post['chip_purchase_send_receipt'])) {
      $data['chip_purchase_send_receipt'] = $this->request->post['chip_purchase_send_receipt'];
    } else {
      $data['chip_purchase_send_receipt'] = $this->config->get('chip_purchase_send_receipt');
    }

    if (isset($this->request->post['chip_due_strict'])) {
      $data['chip_due_strict'] = $this->request->post['chip_due_strict'];
    } else {
      $data['chip_due_strict'] = $this->config->get('chip_due_strict');
    }

    if (isset($this->request->post['chip_due_strict_timing'])) {
      $data['chip_due_strict_timing'] = $this->request->post['chip_due_strict_timing'];
    } else {
      $data['chip_due_strict_timing'] = !empty($this->config->get('chip_due_strict_timing')) ? $this->config->get('chip_due_strict_timing') : '60';
    }

    if (isset($this->request->post['chip_total'])) {
      $data['chip_total'] = $this->request->post['chip_total'];
    } else {
      $data['chip_total'] = $this->config->get('chip_total'); 
    }

    if (isset($this->request->post['chip_paid_order_status_id'])) {
      $data['chip_paid_order_status_id'] = $this->request->post['chip_paid_order_status_id'];
    } else {
      $data['chip_paid_order_status_id'] = $this->config->get('chip_paid_order_status_id');
    }

    if (isset($this->request->post['chip_refunded_order_status_id'])) {
      $data['chip_refunded_order_status_id'] = $this->request->post['chip_refunded_order_status_id'];
    } else {
      $data['chip_refunded_order_status_id'] = $this->config->get('chip_refunded_order_status_id');
    }

    if (isset($this->request->post['chip_allow_instruction'])) {
      $data['chip_allow_instruction'] = $this->request->post['chip_allow_instruction'];
    } else {
      $data['chip_allow_instruction'] = $this->config->get('chip_allow_instruction');
    }

    if (isset($this->request->post['chip_convert_to_processing'])) {
      $data['chip_convert_to_processing'] = $this->request->post['chip_convert_to_processing'];
    } else {
      $data['chip_convert_to_processing'] = $this->config->get('chip_convert_to_processing');
    }

    if (isset($this->request->post['chip_disable_success_redirect'])) {
      $data['chip_disable_success_redirect'] = $this->request->post['chip_disable_success_redirect'];
    } else {
      $data['chip_disable_success_redirect'] = $this->config->get('chip_disable_success_redirect');
    }

    if (isset($this->request->post['chip_disable_success_callback'])) {
      $data['chip_disable_success_callback'] = $this->request->post['chip_disable_success_callback'];
    } else {
      $data['chip_disable_success_callback'] = $this->config->get('chip_disable_success_callback');
    }

    if (isset($this->request->post['chip_debug'])) {
      $data['chip_debug'] = $this->request->post['chip_debug'];
    } else {
      $data['chip_debug'] = $this->config->get('chip_debug');
    }

    $modified_time_zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    if (($key = array_search('Asia/Kuala_Lumpur', $modified_time_zones)) !== false) {
      unset($modified_time_zones[$key]);
      array_unshift($modified_time_zones, 'Asia/Kuala_Lumpur');
    }

    $data['time_zones'] = $modified_time_zones;

    if (isset($this->request->post['chip_time_zone'])) {
      $data['chip_time_zone'] = $this->request->post['chip_time_zone'];
    } else {
      $data['chip_time_zone'] = $this->config->get('chip_time_zone');
    }

    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    $complete_order_status_ids = $this->config->get('config_complete_status');

    foreach($data['order_statuses'] as $order_status){
      foreach($complete_order_status_ids as $complete_order_status_id) {
        if ($order_status['order_status_id'] == $complete_order_status_id) {
          if (isset($data['config_complete_status_name'])) {
            $data['config_complete_status_name'] .= ' / ' . $order_status['name'];
          } else {
            $data['config_complete_status_name'] = $order_status['name'];
          }
          break;
        }
      }
    }

    if (isset($this->request->post['chip_geo_zone_id'])) {
      $data['chip_geo_zone_id'] = $this->request->post['chip_geo_zone_id'];
    } else {
      $data['chip_geo_zone_id'] = $this->config->get('chip_geo_zone_id'); 
    }

    foreach ($languages as $language) {
      if (isset($this->request->post['chip_instruction_'. $language['language_id']])) {
        $data['chip_instruction_' . $language['language_id']] = $this->request->post['chip_instruction_' . $language['language_id']];
      } else {
        $data['chip_instruction_' . $language['language_id']] = $this->config->get('chip_instruction_' . $language['language_id']);
      }

      if (isset($this->request->post['chip_payment_name_'. $language['language_id']])) {
        $data['chip_payment_name_' . $language['language_id']] = $this->request->post['chip_payment_name_' . $language['language_id']];
      } else {
        $data['chip_payment_name_' . $language['language_id']] = $this->config->get('chip_payment_name_' . $language['language_id']);
      }
    }

    $data['languages'] = $languages;

    $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['chip_status'])) {
      $data['chip_status'] = $this->request->post['chip_status'];
    } else {
      $data['chip_status'] = $this->config->get('chip_status');
    }

    if (isset($this->request->post['chip_sort_order'])) {
      $data['chip_sort_order'] = $this->request->post['chip_sort_order'];
    } else {
      $data['chip_sort_order'] = $this->config->get('chip_sort_order');
    }

    $data['webhook'] = HTTPS_CATALOG . 'index.php?route=payment/chip/callback';

    $data['token'] = $this->session->data['token'];

    $this->template = 'payment/chip.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/chip', $data));
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

    return !$this->error;
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