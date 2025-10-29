<?php
namespace Opencart\Catalog\Controller\Extension\Chip\Account;

class Chip extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('extension/chip/account/chip');

		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/payment_method', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$data['list'] = $this->getList();

		$data['types'] = [];

		foreach (['visa', 'mastercard', 'amex', 'discover', 'jcb', 'maestro'] as $type) {
			$data['types'][] = [
				'text'  => $this->language->get('text_' . $type),
				'value' => $type
			];
		}

		$data['months'] = [];

		foreach (range(1, 12) as $month) {
			$data['months'][] = date('m', mktime(0, 0, 0, $month, 1));
		}

		$data['years'] = [];

		foreach (range(date('Y'), date('Y', strtotime('+10 year'))) as $year) {
			$data['years'][] = $year;
		}

		$data['language'] = $this->config->get('config_language');

		$data['customer_token'] = $this->session->data['customer_token'];

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_description'] = $this->language->get('text_description');
		$data['entry_card_name'] = $this->language->get('entry_card_name');
		$data['entry_card_type'] = $this->language->get('entry_card_type');
		$data['entry_card_number'] = $this->language->get('entry_card_number');
		$data['entry_card_expire'] = $this->language->get('entry_card_expire');
		$data['entry_card_cvv'] = $this->language->get('entry_card_cvv');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_month'] = $this->language->get('text_month');
		$data['text_year'] = $this->language->get('text_year');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_add'] = $this->language->get('button_add');
		$data['text_credit_card_add'] = $this->language->get('text_credit_card_add');
		$data['column_credit_card'] = $this->language->get('column_credit_card');
		$data['column_date_expire'] = $this->language->get('column_date_expire');
		$data['column_action'] = $this->language->get('column_action');
		$data['text_no_results'] = $this->language->get('text_no_results');

		return $this->load->view('extension/chip/account/chip', $data);
	}

	/**
	 * List
	 *
	 * @return void
	 */
	public function list(): void {
		$this->load->language('extension/chip/account/chip');

		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/payment_method', 'language=' . $this->config->get('config_language'));

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language'), true));
		}

		$this->response->setOutput($this->getList());
	}

	/**
	 * Get List
	 *
	 * @return string
	 */
	protected function getList(): string {
		$data['credit_cards'] = [];

		$this->load->model('extension/chip/payment/chip');

		$results = $this->model_extension_chip_payment_chip->getTokens($this->customer->getId());

		foreach ($results as $result) {
			$data['credit_cards'][] = [
				'chip_token_id' => $result['chip_token_id'],
                //TODO: Add image
				'image'       => HTTP_SERVER . 'extension/chip/image/' . $result['type'] . '.svg',
				'card_type'   => $this->language->get('text_' . strtolower($result['type'])),
				'type'        => strtolower($result['type']),
				'card_number' => $result['card_number'],
				'date_expire' => $result['card_expire_month'] . '/' . $result['card_expire_year'],
				'delete'      => $this->url->link('extension/chip/account/chip.delete', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&chip_token_id=' . $result['chip_token_id'])
			] + $result;
		}

		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_add'] = $this->language->get('button_add');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['column_credit_card'] = $this->language->get('column_credit_card');
		$data['column_date_expire'] = $this->language->get('column_date_expire');
		$data['column_action'] = $this->language->get('column_action');

		return $this->load->view('extension/chip/account/chip_list', $data);
	}

	public function save(): void {
		$this->load->language('extension/chip/account/chip');

		$json = [];

		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('account/payment_method', 'language=' . $this->config->get('config_language'));

			$json['redirect'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'), true);
		}

		// Chip tokens are automatically saved during payment processing
		// This method is kept for compatibility
		$json['success'] = $this->language->get('text_success');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Delete Credit Card
	 */
	public function delete(): void {
		$this->load->language('extension/chip/account/chip');

		$json = [];

		if (isset($this->request->get['chip_token_id'])) {
			$chip_token_id = (int)$this->request->get['chip_token_id'];
		} else {
			$chip_token_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$json['error'] = $this->language->get('error_logged');
		}

		$this->load->model('extension/chip/payment/chip');

		$token_info = $this->model_extension_chip_payment_chip->getToken($this->customer->getId(), $chip_token_id);

		if (!$token_info) {
			$json['error'] = $this->language->get('error_token');
		}

		if (!$json) {
			$this->model_extension_chip_payment_chip->deleteToken($this->customer->getId(), $chip_token_id);

			$json['success'] = $this->language->get('text_delete');

			// Clear payment and shipping methods
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

