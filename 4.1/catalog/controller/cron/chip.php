<?php
namespace Opencart\Catalog\Controller\Extension\Chip\Cron;

class Chip extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/chip/payment/chip');

		$error = [];

		if (isset($this->session->data['order_id'])) {
			$order_id = (int)$this->session->data['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$this->load->model('checkout/subscription');

			$subscription_info = $this->model_checkout_subscription->getSubscription($order_info['subscription_id']);

			if (!$subscription_info) {
				$error['subscription'] = $this->language->get('error_subscription');
			}

			// Get CHIP token ID from payment method code (e.g., 'chip.123' -> '123')
			$payment_code = $order_info['payment_method']['code'];
			$chip_token_id = str_replace('chip.', '', $payment_code);

			if ($chip_token_id == 'chip' || empty($chip_token_id)) {
				$error['chip_token'] = $this->language->get('error_token');
			}

			$this->load->model('extension/chip/payment/chip');

			$token_info = $this->model_extension_chip_payment_chip->getToken($order_info['customer_id'], (int)$chip_token_id);

			if (!$token_info) {
				$error['chip_token'] = $this->language->get('error_token');
			}
		} else {
			$error['order'] = $this->language->get('error_order');
		}

		if (!$error) {
			// Create purchase for charging stored token
			$success_callback_url = $this->url->link('extension/chip/payment/chip.success_callback');
			$success_callback_url .= (strpos($success_callback_url, '?') !== false ? '&' : '?') . 'order_id=' . $order_id;

			$success_redirect_url = $this->url->link('extension/chip/payment/chip.success_redirect');
			$success_redirect_url .= (strpos($success_redirect_url, '?') !== false ? '&' : '?') . 'order_id=' . $order_id;

			$params = array(
				'success_callback' => $success_callback_url,
				'success_redirect' => $success_redirect_url,
				'failure_redirect' => $this->url->link('account/subscription', 'language=' . $this->config->get('config_language')),
				'cancel_redirect'  => $this->url->link('account/subscription', 'language=' . $this->config->get('config_language')),
				'creator_agent'    => 'OC41: 1.0.0',
				'reference'        => $order_id,
				'platform'         => 'opencart',
				'brand_id'         => $this->config->get('payment_chip_brand_id'),
				'client'           => array(),
				'purchase'         => array(
					'timezone'       => $this->config->get('payment_chip_time_zone'),
					'currency'       => 'MYR',
					'due_strict'     => $this->config->get('payment_chip_due_strict'),
					'products'       => array(),
				),
			);

			// Get products for the order
			$products = $this->model_checkout_order->getProducts($order_id);

			foreach ($products as $product) {
				$product_price = $this->currency->convert($product['price'], $this->config->get('config_currency'), 'MYR');

				$params['purchase']['products'][] = array(
					'name' => substr($product['name'], 0, 256),
					'quantity' => $product['quantity'],
					'price' => round($product_price * 100),
					'category' => $product['product_id']
				);
			}

			// Add client information
			if (!empty($order_info['email'])) {
				$params['client']['email'] = $order_info['email'];
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

			$this->model_extension_chip_payment_chip->setKeys($this->config->get('payment_chip_secret_key'), 'brand-id');

			// Create purchase
			$purchase = $this->model_extension_chip_payment_chip->createPurchase($params);

			if (!array_key_exists('id', $purchase)) {
				$error['purchase'] = $this->language->get('error_purchase');
				
				if ($this->config->get('payment_chip_debug')) {
					$this->log->write('CHIP API /purchase/ failed for order #' . $order_id . '. Response Body: ' . json_encode($purchase));
				}
			} else {
				$purchase_id = $purchase['id'];

				// Charge the stored token
				$charge_response = $this->model_extension_chip_payment_chip->chargeToken($purchase_id, $token_info['token_id']);

				// Save to chip_report table
				$customer_id = $order_info['customer_id'];
				$status = isset($purchase['status']) ? $purchase['status'] : 'pending';
				
				$total = $order_info['total'];
				if ($this->config->get('config_currency') != 'MYR') {
					$total = $this->currency->convert($order_info['total'], $this->config->get('config_currency'), 'MYR');
				}
				$amount = $total;

				$this->model_extension_chip_payment_chip->addReport(array(
					'customer_id' => $customer_id,
					'chip_id' => $purchase_id,
					'order_id' => $order_id,
					'status' => $status,
					'amount' => $amount,
					'environment_type' => isset($purchase['is_test']) && $purchase['is_test'] ? 'staging' : 'production'
				));

				if (isset($charge_response['id']) && isset($charge_response['status']) && $charge_response['status'] == 'paid') {
					$order_status_id = (int)$this->config->get('payment_chip_paid_order_status_id');
				} else {
					$order_status_id = (int)$this->config->get('payment_chip_failed_order_status_id');
				}

				// If payment order status is active or processing
				if (in_array($order_status_id, (array)$this->config->get('config_processing_status') + (array)$this->config->get('config_complete_status'))) {
					$remaining = 0;
					$date_next = '';

					if ($subscription_info['trial_status'] && $subscription_info['trial_remaining'] > 1) {
						$remaining = $subscription_info['trial_remaining'] - 1;
						$date_next = date('Y-m-d', strtotime('+' . $subscription_info['trial_cycle'] . ' ' . $subscription_info['trial_frequency']));

						$this->model_checkout_subscription->editTrialRemaining($subscription_info['subscription_id'], $remaining);
					} elseif ($subscription_info['duration'] && $subscription_info['remaining']) {
						$remaining = $subscription_info['remaining'] - 1;
						$date_next = date('Y-m-d', strtotime('+' . $subscription_info['cycle'] . ' ' . $subscription_info['frequency']));

						// If duration make sure there is remaining
						$this->model_checkout_subscription->editRemaining($subscription_info['subscription_id'], $remaining);
					} elseif (!$subscription_info['duration']) {
						// If duration is unlimited
						$date_next = date('Y-m-d', strtotime('+' . $subscription_info['cycle'] . ' ' . $subscription_info['frequency']));
					}

					if ($date_next) {
						$this->model_checkout_subscription->editDateNext($order_info['subscription_id'], $date_next);
					}

					$this->model_checkout_subscription->addHistory($order_info['subscription_id'], $this->config->get('config_subscription_active_status_id'), $this->language->get('text_success'));
				} else {
					// If payment failed change subscription history to failed
					$this->model_checkout_subscription->addHistory($order_info['subscription_id'], $this->config->get('config_subscription_failed_status_id'), $this->language->get('error_payment_failed'));
				}

				$this->model_checkout_order->addHistory($order_id, $order_status_id);
			}
		} else {
			$this->model_checkout_order->addHistory($order_id, $this->config->get('config_failed_status_id'));

			// Add subscription history failed if payment method for cron didn't exist
			$this->model_checkout_subscription->addHistory($order_info['subscription_id'], $this->config->get('config_subscription_failed_status_id'), $this->language->get('text_log'));

			// Log errors
			foreach ($error as $key => $value) {
				$this->model_checkout_subscription->addLog($order_info['subscription_id'], $key, $value);
			}
		}
	}
}

