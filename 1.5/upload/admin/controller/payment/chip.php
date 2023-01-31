<?php
class ControllerPaymentChip extends Controller {
  private $error = array();

  public function index() {
    $this->language->load( 'payment/chip' );

    $this->document->setTitle( $this->language->get( 'heading_title' ) );

    $this->load->model('setting/setting');

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

    $this->data['entry_secret_key'] = $this->language->get('entry_secret_key');
    $this->data['entry_brand_id'] = $this->language->get('entry_brand_id');
    $this->data['entry_purchase_send_receipt'] = $this->language->get('entry_purchase_send_receipt');
    $this->data['entry_due_strict'] = $this->language->get('entry_due_strict');
    $this->data['entry_due_strict_timing'] = $this->language->get('entry_callback');

    $this->data['entry_total'] = $this->language->get('entry_total');
    $this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
    $this->data['entry_status'] = $this->language->get('entry_status');
    $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

    $this->data['entry_created_order_status'] = $this->language->get('entry_created_order_status');
    $this->data['entry_error_order_status'] = $this->language->get('entry_error_order_status');
    $this->data['entry_cancelled_order_status'] = $this->language->get('entry_cancelled_order_status');
    $this->data['entry_overdue_order_status'] = $this->language->get('entry_overdue_order_status');
    $this->data['entry_paid_order_status'] = $this->language->get('entry_paid_order_status');
    $this->data['entry_refunded_order_status'] = $this->language->get('entry_refunded_order_status');

    $this->data['entry_allow_notes'] = $this->language->get('entry_allow_notes');
    $this->data['entry_logo'] = $this->language->get('entry_logo');

    $this->data['button_save'] = $this->language->get('button_save');
    $this->data['button_cancel'] = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $this->data['error_warning'] = $this->error['warning'];
    } else {
      $this->data['error_warning'] = '';
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
      $this->data['chip_due_strict_timing'] = $this->config->get('chip_due_strict_timing');
    }

    if (isset($this->request->post['chip_total'])) {
      $this->data['chip_total'] = $this->request->post['chip_total'];
    } else {
      $this->data['chip_total'] = $this->config->get('chip_total'); 
    } 

    if (isset($this->request->post['chip_created_order_status_id'])) {
      $this->data['chip_created_order_status_id'] = $this->request->post['chip_created_order_status_id'];
    } else {
      $this->data['chip_created_order_status_id'] = $this->config->get('chip_created_order_status_id');
    }

    if (isset($this->request->post['chip_viewed_order_status_id'])) {
      $this->data['chip_viewed_order_status_id'] = $this->request->post['chip_viewed_order_status_id'];
    } else {
      $this->data['chip_viewed_order_status_id'] = $this->config->get('chip_viewed_order_status_id');
    }

    if (isset($this->request->post['chip_error_order_status_id'])) {
      $this->data['chip_error_order_status_id'] = $this->request->post['chip_error_order_status_id'];
    } else {
      $this->data['chip_error_order_status_id'] = $this->config->get('chip_error_order_status_id');
    }

    if (isset($this->request->post['chip_cancelled_order_status_id'])) {
      $this->data['chip_cancelled_order_status_id'] = $this->request->post['chip_cancelled_order_status_id'];
    } else {
      $this->data['chip_cancelled_order_status_id'] = $this->config->get('chip_cancelled_order_status_id');
    }

    if (isset($this->request->post['chip_overdue_order_status_id'])) {
      $this->data['chip_overdue_order_status_id'] = $this->request->post['chip_overdue_order_status_id'];
    } else {
      $this->data['chip_overdue_order_status_id'] = $this->config->get('chip_overdue_order_status_id');
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

    if (isset($this->request->post['chip_allow_note'])) {
      $this->data['chip_allow_note'] = $this->request->post['chip_allow_note'];
    } else {
      $this->data['chip_allow_note'] = $this->config->get('chip_allow_note');
    }

    $this->load->model('localisation/order_status');

    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    if (isset($this->request->post['chip_geo_zone_id'])) {
      $this->data['chip_geo_zone_id'] = $this->request->post['chip_geo_zone_id'];
    } else {
      $this->data['chip_geo_zone_id'] = $this->config->get('chip_geo_zone_id'); 
    }

    $this->load->model('tool/image');

    $logo = $this->config->get('chip_logo');

    if (isset($this->request->post['chip_logo']) && file_exists(DIR_IMAGE . $this->request->post['chip_logo'])) {
      $this->data['thumb'] = $this->model_tool_image->resize($this->request->post['chip_logo'], 750, 90);
    } elseif(($logo != '') && file_exists(DIR_IMAGE . $logo)) {
      $this->data['thumb'] = $this->model_tool_image->resize($logo, 750, 90);
    } else {
      $this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 750, 90);
    }

    $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 750, 90);

    $this->load->model('localisation/geo_zone');

    $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['chip_status'])) {
      $this->data['chip_status'] = $this->request->post['chip_status'];
    } else {
      $this->data['chip_status'] = $this->config->get('chip_status');
    }

    if (isset($this->request->post['chip_logo'])) {
      $this->data['chip_logo'] = $this->request->post['chip_logo'];
    } else {
      $this->data['chip_logo'] = $this->config->get('chip_logo');
    }

    if (isset($this->request->post['chip_sort_order'])) {
      $this->data['chip_sort_order'] = $this->request->post['chip_sort_order'];
    } else {
      $this->data['chip_sort_order'] = $this->config->get('chip_sort_order');
    }

    $this->data['token'] = $this->session->data['token'];

    $this->template = 'payment/chip.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->response->setOutput($this->render());
  }

  public function imageLogo() {
    $this->load->model('tool/image');

    if (isset($this->request->get['image'])) {
      $this->response->setOutput($this->model_tool_image->resize(html_entity_decode($this->request->get['image'], ENT_QUOTES, 'UTF-8'), 750, 90));
    }
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'payment/chip')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->request->post['chip_secret_key']) {
      $this->error['secret_key'] = $this->language->get('error_secret_key');
    }

    if (!$this->request->post['chip_brand_id']) {
      $this->error['brand_id'] = $this->language->get('error_brand_id');
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
}