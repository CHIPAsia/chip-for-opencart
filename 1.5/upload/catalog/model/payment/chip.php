<?php
class ModelPaymentChip extends Model {
  public function getMethod($address, $total) {
    $this->language->load('payment/chip');

    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('chip_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

    if ($this->config->get('chip_total') > 0 && $this->config->get('chip_total') > $total) {
      $status = false;
    } elseif (!$this->config->get('chip_geo_zone_id')) {
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
        'title'      => nl2br($this->config->get('chip_payment_name_' . $this->config->get('config_language_id'))),
        'sort_order' => $this->config->get('chip_sort_order')
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

    // this to prevent error when account balance called
    if ($this->require_empty_string_encoding){
      curl_setopt($ch, CURLOPT_ENCODING, '');
    }

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
  }
}
