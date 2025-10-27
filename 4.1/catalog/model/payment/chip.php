<?php
namespace Opencart\Catalog\Model\Extension\Chip\Payment;
class Chip extends \Opencart\System\Engine\Model {
  private $private_key;
  private $brand_id;
  public function getMethod($address): array {
    $this->load->language('extension/chip/payment/chip');

    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_chip_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

    if ($this->cart->hasSubscription()) {
			$status = false;
    } elseif (!$this->config->get('payment_chip_geo_zone_id')) {
      $status = true;
    } elseif ($query->num_rows) {
      $status = true;
    } else {
      $status = false;
    }

    $method_data = array();

    if ($status) {
      $method_data = array(
        'code'       => 'chip',
        'title'      => nl2br($this->config->get('payment_chip_payment_name_' . $this->config->get('config_language_id'))),
        'sort_order' => $this->config->get('payment_chip_sort_order')
      );
    }

    return $method_data;
  }

  public function set_keys($private_key, $brand_id) {
    $this->private_key = $private_key;
    $this->brand_id    = $brand_id;
  }

  public function create_purchase($params)
  {
    return $this->call('POST', '/purchases/', $params);
  }

  public function get_purchase($purchase_id)
  {
    return $this->call('GET', "/purchases/{$purchase_id}/");
  }

  public function create_client($params) 
  {
    return $this->call('POST', "/clients/", $params);
  }

  // this is secret feature
  public function get_client_by_email($email)
  {
    $email_encoded = urlencode($email);
    return $this->call('GET', "/clients/?q={$email_encoded}");
  }

  public function addReport($data)
  {
    $this->db->query("INSERT INTO `" . DB_PREFIX . "chip_report` 
      (`customer_id`, `chip_id`, `order_id`, `status`, `amount`, `environment_type`, `date_added`) 
      VALUES (" . (int)$data['customer_id'] . ", " . (int)$data['chip_id'] . ", " . (int)$data['order_id'] . ", 
      '" . $this->db->escape($data['status']) . "', '" . (float)$data['amount'] . "', 
      '" . $this->db->escape($data['environment_type']) . "', NOW())");
  }

  public function updateReportStatus($chip_id, $status)
  {
    $this->db->query("UPDATE `" . DB_PREFIX . "chip_report` 
      SET `status` = '" . $this->db->escape($status) . "' 
      WHERE `chip_id` = " . (int)$chip_id);
  }

  private function call($method, $route, $params = [])
  {
    $private_key = $this->private_key;
    if (!empty($params)) {
      $params = json_encode($params);
    }

    $response = $this->request(
      $method,
      sprintf("%s/api/v1%s", 'https://gate.chip-in.asia', $route),
      $params,
      [
        'Content-type: application/json',
        'Authorization: ' . "Bearer " . $private_key,
      ]
    );

    $result = json_decode($response, true);
    if (!$result) {
      return null;
    }

    if (!empty($result['errors'])) {
      return null;
    }

    return $result;
  }

  private function request($method, $url, $params = [], $headers = [])
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    if ($method == 'POST') {
      curl_setopt($ch, CURLOPT_POST, 1);
    }

    if ($method == 'PUT') {
      curl_setopt($ch, CURLOPT_PUT, 1);
    }

    if ($method == 'PUT' or $method == 'POST') {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
  }
}
