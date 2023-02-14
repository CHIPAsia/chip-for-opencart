<?php
class ControllerExtensionPaymentChip extends Controller {
  private $error = array();

  public function index() {
    $this->load->language( 'extension/payment/chip' );

    $this->document->setTitle( $this->language->get( 'heading_title' ) );

    $this->load->model('setting/setting');
    $this->load->model('localisation/order_status');
    $this->load->model('localisation/geo_zone');
    $this->load->model('localisation/language');

    if ( ( $this->request->server['REQUEST_METHOD'] == 'POST' ) && $this->validate() ) {
      $this->model_setting_setting->editSetting('payment_chip', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
    } else {
      $data['error'] = @$this->error;
    }

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    $languages = $this->model_localisation_language->getLanguages();

    if (isset($this->error['instruction'])) {
      $data['error_instruction'] = $this->error['instruction'];
    } else {
      $data['error_instruction'] = array();
    }

    if (isset($this->error['payment_name'])) {
      $data['error_payment_name'] = $this->error['payment_name'];
    } else {
      $data['error_payment_name'] = array();
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
      'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
    );

    $data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_extension'),
      'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
    );

    $data['breadcrumbs'][] = array(
      'text'      => $this->language->get('heading_title'),
      'href'      => $this->url->link('extension/payment/chip', 'user_token=' . $this->session->data['user_token'], true),
    );

    $data['action'] = $this->url->link('extension/payment/chip', 'user_token=' . $this->session->data['user_token'], true);

    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

    if (isset($this->request->post['payment_chip_secret_key'])) {
      $data['payment_chip_secret_key'] = $this->request->post['payment_chip_secret_key'];
    } else {
      $data['payment_chip_secret_key'] = $this->config->get('payment_chip_secret_key');
    }

    if (isset($this->request->post['payment_chip_brand_id'])) {
      $data['payment_chip_brand_id'] = $this->request->post['payment_chip_brand_id'];
    } else {
      $data['payment_chip_brand_id'] = $this->config->get('payment_chip_brand_id');
    }

    if (isset($this->request->post['payment_chip_public_key'])) {
      $data['payment_chip_public_key'] = $this->request->post['payment_chip_public_key'];
    } else {
      $data['payment_chip_public_key'] = $this->config->get('payment_chip_public_key');
    }

    if (isset($this->request->post['payment_chip_general_public_key'])) {
      $data['payment_chip_general_public_key'] = $this->request->post['payment_chip_general_public_key'];
    } else {
      $data['payment_chip_general_public_key'] = $this->config->get('payment_chip_general_public_key');
    }

    if (isset($this->request->post['payment_chip_purchase_send_receipt'])) {
      $data['payment_chip_purchase_send_receipt'] = $this->request->post['payment_chip_purchase_send_receipt'];
    } else {
      $data['payment_chip_purchase_send_receipt'] = $this->config->get('payment_chip_purchase_send_receipt');
    }

    if (isset($this->request->post['payment_chip_due_strict'])) {
      $data['payment_chip_due_strict'] = $this->request->post['payment_chip_due_strict'];
    } else {
      $data['payment_chip_due_strict'] = $this->config->get('payment_chip_due_strict');
    }

    if (isset($this->request->post['payment_chip_due_strict_timing'])) {
      $data['payment_chip_due_strict_timing'] = $this->request->post['payment_chip_due_strict_timing'];
    } else {
      $data['payment_chip_due_strict_timing'] = !empty($this->config->get('payment_chip_due_strict_timing')) ? $this->config->get('payment_chip_due_strict_timing') : '60';
    }

    if (isset($this->request->post['payment_chip_total'])) {
      $data['payment_chip_total'] = $this->request->post['payment_chip_total'];
    } else {
      $data['payment_chip_total'] = $this->config->get('payment_chip_total'); 
    }

    if (isset($this->request->post['payment_chip_canceled_order_status_id'])) {
      $data['payment_chip_canceled_order_status_id'] = $this->request->post['payment_chip_canceled_order_status_id'];
    } else {
      $data['payment_chip_canceled_order_status_id'] = $this->config->get('payment_chip_canceled_order_status_id');
    }

    if (isset($this->request->post['payment_chip_failed_order_status_id'])) {
      $data['payment_chip_failed_order_status_id'] = $this->request->post['payment_chip_failed_order_status_id'];
    } else {
      $data['payment_chip_failed_order_status_id'] = $this->config->get('payment_chip_failed_order_status_id');
    }

    if (isset($this->request->post['payment_chip_paid_order_status_id'])) {
      $data['payment_chip_paid_order_status_id'] = $this->request->post['payment_chip_paid_order_status_id'];
    } else {
      $data['payment_chip_paid_order_status_id'] = $this->config->get('payment_chip_paid_order_status_id');
    }

    if (isset($this->request->post['payment_chip_refunded_order_status_id'])) {
      $data['payment_chip_refunded_order_status_id'] = $this->request->post['payment_chip_refunded_order_status_id'];
    } else {
      $data['payment_chip_refunded_order_status_id'] = $this->config->get('payment_chip_refunded_order_status_id');
    }

    if (isset($this->request->post['payment_chip_allow_instruction'])) {
      $data['payment_chip_allow_instruction'] = $this->request->post['payment_chip_allow_instruction'];
    } else {
      $data['payment_chip_allow_instruction'] = $this->config->get('payment_chip_allow_instruction');
    }

    if (isset($this->request->post['payment_chip_convert_to_processing'])) {
      $data['payment_chip_convert_to_processing'] = $this->request->post['payment_chip_convert_to_processing'];
    } else {
      $data['payment_chip_convert_to_processing'] = $this->config->get('payment_chip_convert_to_processing');
    }

    if (isset($this->request->post['payment_chip_disable_success_redirect'])) {
      $data['payment_chip_disable_success_redirect'] = $this->request->post['payment_chip_disable_success_redirect'];
    } else {
      $data['payment_chip_disable_success_redirect'] = $this->config->get('payment_chip_disable_success_redirect');
    }

    if (isset($this->request->post['payment_chip_disable_success_callback'])) {
      $data['payment_chip_disable_success_callback'] = $this->request->post['payment_chip_disable_success_callback'];
    } else {
      $data['payment_chip_disable_success_callback'] = $this->config->get('payment_chip_disable_success_callback');
    }

    if (isset($this->request->post['payment_chip_debug'])) {
      $data['payment_chip_debug'] = $this->request->post['payment_chip_debug'];
    } else {
      $data['payment_chip_debug'] = $this->config->get('payment_chip_debug');
    }

    $modified_time_zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    if (($key = array_search('Asia/Kuala_Lumpur', $modified_time_zones)) !== false) {
      unset($modified_time_zones[$key]);
      array_unshift($modified_time_zones, 'Asia/Kuala_Lumpur');
    }

    $data['time_zones'] = $modified_time_zones;

    if (isset($this->request->post['payment_chip_time_zone'])) {
      $data['payment_chip_time_zone'] = $this->request->post['payment_chip_time_zone'];
    } else {
      $data['payment_chip_time_zone'] = $this->config->get('payment_chip_time_zone');
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

    if (isset($this->request->post['payment_chip_geo_zone_id'])) {
      $data['payment_chip_geo_zone_id'] = $this->request->post['payment_chip_geo_zone_id'];
    } else {
      $data['payment_chip_geo_zone_id'] = $this->config->get('payment_chip_geo_zone_id'); 
    }

    $data['payment_chip_payment_name'] = array();

    foreach ($languages as $language) {
      if (isset($this->request->post['payment_chip_instruction_'. $language['language_id']])) {
        $data['payment_chip_instruction'][$language['language_id']] = $this->request->post['payment_chip_instruction_' . $language['language_id']];
      } else {
        $data['payment_chip_instruction'][$language['language_id']] = $this->config->get('payment_chip_instruction_' . $language['language_id']);
      }

      if (isset($this->request->post['payment_chip_payment_name_'. $language['language_id']])) {
        $data['payment_chip_payment_name'][$language['language_id']] = $this->request->post['payment_chip_payment_name_' . $language['language_id']];
      } else {
        $data['payment_chip_payment_name'][$language['language_id']] = $this->config->get('payment_chip_payment_name_' . $language['language_id']);
      }
    }

    $data['languages'] = $languages;

    $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['payment_chip_status'])) {
      $data['payment_chip_status'] = $this->request->post['payment_chip_status'];
    } else {
      $data['payment_chip_status'] = $this->config->get('payment_chip_status');
    }

    if (isset($this->request->post['payment_chip_sort_order'])) {
      $data['payment_chip_sort_order'] = $this->request->post['payment_chip_sort_order'];
    } else {
      $data['payment_chip_sort_order'] = $this->config->get('payment_chip_sort_order');
    }

    $data['formatted_help_paid_order_status'] = sprintf($this->language->get('help_paid_order_status'), $data['config_complete_status_name']);

    $data['canceled_behaviors'] = array(
      'missing_order' => $this->language->get('behavior_missing_order'),
      'cancel_order' => $this->language->get('behavior_cancel_order'),
    );

    $data['failed_behaviors'] = array(
      'missing_order' => $this->language->get('behavior_missing_order'),
      'fail_order' => $this->language->get('behavior_fail_order'),
    );

    $data['webhook'] = HTTPS_CATALOG . 'index.php?route=extension/payment/chip/callback';

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/payment/chip', $data));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/chip')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    $this->load->model('localisation/language');

    $languages = $this->model_localisation_language->getLanguages();

    foreach ($languages as $language) {
      if (isset($this->request->post['payment_chip_allow_instruction']) && $this->request->post['payment_chip_allow_instruction'] == '1' && empty($this->request->post['payment_chip_instruction_'. $language['language_id']])) {
        $this->error['instruction'][$language['language_id']] = $this->language->get('error_instruction');
      }

      if (empty($this->request->post['payment_chip_payment_name_'. $language['language_id']])) {
        $this->error['payment_name'][$language['language_id']] = $this->language->get('error_payment_name');
      }
    }

    if ($this->request->post['payment_chip_secret_key']) {
      $this->configure_general_public_key();
    } else {
      $this->error['secret_key'] = $this->language->get('error_secret_key');
    }

    if (!$this->request->post['payment_chip_due_strict_timing']) {
      $this->error['due_strict_timing'] = $this->language->get('error_due_strict_timing');
    }

    if (!$this->request->post['payment_chip_brand_id']) {
      $this->error['brand_id'] = $this->language->get('error_brand_id');
    }

    if ($this->request->post['payment_chip_public_key']) {
      $public_key_validity = openssl_pkey_get_public($this->request->post['payment_chip_public_key']);

      if (!$public_key_validity) {
        $this->error['public_key'] = $this->language->get('error_public_key');
      }
    }

    return !$this->error;
  }

  public function install() {
    $this->load->model('extension/payment/chip');
    $this->model_extension_payment_chip->install();
  }

  public function uninstall() {
    $this->load->model('extension/payment/chip');
    $this->model_extension_payment_chip->uninstall();
  }

  private function configure_general_public_key() {
    $this->load->model('extension/payment/chip');
    $this->model_extension_payment_chip->set_keys($this->request->post['payment_chip_secret_key'], '');
    $general_public_key = str_replace('\n', "\n", $this->model_extension_payment_chip->get_public_key());

    if (isset($general_public_key['__all__'])) {
      $this->error['secret_key'] = implode('. ', $general_public_key['__all__'][0]);
      return false;
    }

    if (empty($general_public_key) OR !openssl_pkey_get_public($general_public_key)){
      $this->error['secret_key'] = $this->language->get('error_secret_key_invalid');
      return false;
    }

    $this->request->post['payment_chip_general_public_key'] = $general_public_key;
    return true;
  }
}