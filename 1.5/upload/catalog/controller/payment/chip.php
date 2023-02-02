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
}