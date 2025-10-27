<?php
namespace Opencart\Catalog\Model\Extension\Chip\Payment;

class Chip extends \Opencart\System\Engine\Model {
  private string $private_key;
  private string $brand_id;

  public function getMethods(array $address): array {
    $this->load->language('extension/chip/payment/chip');

    if ($this->cart->hasSubscription()) {
      return [];
    }

    $geo_zone_id = $this->config->get('payment_chip_geo_zone_id');

    if (!$geo_zone_id) {
      $status = true;
    } else {
      $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");
      
      $status = (bool)$query->num_rows;
    }

    if (!$status) {
      return [];
    }

    return [
      'code'       => 'chip',
      'title'      => nl2br($this->config->get('payment_chip_payment_name_' . $this->config->get('config_language_id'))),
      'sort_order' => $this->config->get('payment_chip_sort_order')
    ];
  }

  public function setKeys(string $private_key, string $brand_id): void {
    $this->private_key = $private_key;
    $this->brand_id = $brand_id;
  }

  public function createPurchase(array $params): array {
    return $this->call('POST', '/purchases/', $params);
  }

  public function getPurchase(string $purchase_id): array {
    return $this->call('GET', "/purchases/{$purchase_id}/");
  }


  public function addReport(array $data): void {
    $this->db->query("INSERT INTO `" . DB_PREFIX . "chip_report` 
      (`customer_id`, `chip_id`, `order_id`, `status`, `amount`, `environment_type`, `date_added`) 
      VALUES (" . (int)$data['customer_id'] . ", " . (int)$data['chip_id'] . ", " . (int)$data['order_id'] . ", 
      '" . $this->db->escape($data['status']) . "', '" . (float)$data['amount'] . "', 
      '" . $this->db->escape($data['environment_type']) . "', NOW())");
  }

  public function updateReportStatus(int $chip_id, string $status): void {
    $this->db->query("UPDATE `" . DB_PREFIX . "chip_report` 
      SET `status` = '" . $this->db->escape($status) . "' 
      WHERE `chip_id` = " . (int)$chip_id);
  }

  public function addToken(array $data): void {
    $this->db->query("INSERT INTO `" . DB_PREFIX . "chip_token` 
      (`customer_id`, `token_id`, `type`, `card_name`, `card_number`, `card_expire_month`, `card_expire_year`, `date_added`) 
      VALUES (" . (int)$data['customer_id'] . ", 
      '" . $this->db->escape($data['token_id']) . "', 
      '" . $this->db->escape($data['type']) . "', 
      '" . $this->db->escape($data['card_name']) . "', 
      '" . $this->db->escape($data['card_number']) . "', 
      '" . $this->db->escape($data['card_expire_month']) . "', 
      '" . $this->db->escape($data['card_expire_year']) . "', 
      NOW())");
  }

  private function call(string $method, string $route, array $params = []): ?array {
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

  private function request(string $method, string $url, string $params = '', array $headers = []): string {
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
