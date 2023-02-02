<?php
class ModelPaymentChip extends Model
{

  private $require_empty_string_encoding = false;
  private $private_key;
  private $brand_id;

  public function install() {
    /*
    $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "chip_order` (
        `chip_order_id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `created` DATETIME NOT NULL,
        `modified` DATETIME NOT NULL,
        `capture_status` ENUM('Complete','NotComplete') DEFAULT NULL,
        `currency_code` CHAR(3) NOT NULL,
        `authorization_id` VARCHAR(30) NOT NULL,
        `total` DECIMAL( 10, 2 ) NOT NULL,
        PRIMARY KEY (`chip_order_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

    $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "chip_order_transaction` (
        `chip_order_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
        `chip_order_id` int(11) NOT NULL,
        `transaction_id` CHAR(50) NOT NULL,
        `parent_transaction_id` CHAR(50) NOT NULL,
        `created` DATETIME NOT NULL,
        `note` VARCHAR(255) NOT NULL,
        `msgsubid` CHAR(38) NOT NULL,
        `receipt_id` CHAR(20) NOT NULL,
        `payment_type` ENUM('none','echeck','instant', 'refund', 'void') DEFAULT NULL,
        `payment_status` CHAR(20) NOT NULL,
        `transaction_entity` CHAR(50) NOT NULL,
        `amount` DECIMAL( 10, 2 ) NOT NULL,
        `debug_data` TEXT NOT NULL,
        `call_data` TEXT NOT NULL,
        PRIMARY KEY (`chip_order_transaction_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
      */
  }

  public function uninstall() {
    /*
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "chip_order_transaction`;");
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "chip_order`;");
    */
  }

  public function totalCaptured($chip_order_id) {
    /*
    $qry = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "chip_order_transaction` WHERE `chip_order_id` = '" . (int)$chip_order_id . "' AND (`payment_status` = 'refunded' OR `payment_status` = 'paid' OR `payment_status` = 'hold') AND `transaction_entity` = 'purchase'");

    return $qry->row['amount'];
    */
  }

  public function totalRefundedOrder($chip_order_id) {
    /*
    $qry = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "chip_order_transaction` WHERE `chip_order_id` = '" . (int)$chip_order_id . "' AND `payment_status` = 'refunded'");

    return $qry->row['amount'];
    */
  }

  public function totalRefundedTransaction($transaction_id) {
    /*
    $qry = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "chip_order_transaction` WHERE `parent_transaction_id` = '" . $this->db->escape($transaction_id) . "' AND `payment_type` = 'refund'");

    return $qry->row['amount'];
    */
  }

  public function log($data, $title = null) {
    /*
    if ($this->config->get('chip_debug')) {
      $this->log->write('CHIP debug (' . $title . '): ' . json_encode($data));
    }
    */
  }

  public function getOrder($order_id) {
    /*
    $qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "chip_order` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");

    if ($qry->num_rows) {
      $order = $qry->row;
      $order['transactions'] = $this->getTransactions($order['chip_order_id']);
      $order['captured'] = $this->totalCaptured($order['chip_order_id']);
      return $order;
    } else {
      return false;
    }
    */
  }

  public function updateOrder($capture_status, $order_id) {
    /*
    $this->db->query("UPDATE `" . DB_PREFIX . "chip_order` SET `modified` = now(), `capture_status` = '" . $this->db->escape($capture_status) . "' WHERE `order_id` = '" . (int)$order_id . "'");
    */
  }

  public function addTransaction($transaction_data, $request_data = array()) {
    /*
    $this->db->query("INSERT INTO `" . DB_PREFIX . "chip_order_transaction` SET `chip_order_id` = '" . (int)$transaction_data['chip_order_id'] . "', `transaction_id` = '" . $this->db->escape($transaction_data['transaction_id']) . "', `parent_transaction_id` = '" . $this->db->escape($transaction_data['parent_transaction_id']) . "', `created` = NOW(), `note` = '" . $this->db->escape($transaction_data['note']) . "', `msgsubid` = '" . $this->db->escape($transaction_data['msgsubid']) . "', `receipt_id` = '" . $this->db->escape($transaction_data['receipt_id']) . "', `payment_type` = '" . $this->db->escape($transaction_data['payment_type']) . "', `payment_status` = '" . $this->db->escape($transaction_data['payment_status']) . "', `transaction_entity` = '" . $this->db->escape($transaction_data['transaction_entity']) . "', `amount` = '" . (double)$transaction_data['amount'] . "', `debug_data` = '" . $this->db->escape($transaction_data['debug_data']) . "'");

    $chip_order_transaction_id = $this->db->getLastId();

    if ($request_data) {
      $serialized_data = serialize($request_data);

      $this->db->query("
        UPDATE " . DB_PREFIX . "chip_order_transaction
        SET call_data = '" . $this->db->escape($serialized_data) . "'
        WHERE chip_order_transaction_id = " . (int)$chip_order_transaction_id . "
        LIMIT 1
      ");
    }

    return $chip_order_transaction_id;
    */
  }

  public function getFailedTransaction($chip_order_transaction_id) {
    /*
    $result = $this->db->query("
      SELECT *
      FROM " . DB_PREFIX . "chip_order_transaction
      WHERE chip_order_transaction_id = " . (int)$chip_order_transaction_id . "
    ")->row;

    if ($result) {
      return $result;
    } else {
      return false;
    }
    */
  }

  public function updateTransaction($transaction) {
    /*
    $this->db->query("
      UPDATE " . DB_PREFIX . "chip_order_transaction
      SET chip_order_id = " . (int)$transaction['chip_order_id'] . ",
        transaction_id = '" . $this->db->escape($transaction['transaction_id']) . "',
        parent_transaction_id = '" . $this->db->escape($transaction['parent_transaction_id']) . "',
        created = '" . $this->db->escape($transaction['created']) . "',
        note = '" . $this->db->escape($transaction['note']) . "',
        msgsubid = '" . $this->db->escape($transaction['msgsubid']) . "',
        receipt_id = '" . $this->db->escape($transaction['receipt_id']) . "',
        payment_type = '" . $this->db->escape($transaction['payment_type']) . "',
        payment_status = '" . $this->db->escape($transaction['payment_status']) . "',
        transaction_entity = '" . $this->db->escape($transaction['transaction_entity']) . "',
        amount = '" . $this->db->escape($transaction['amount']) . "',
        debug_data = '" . $this->db->escape($transaction['debug_data']) . "',
        call_data = '" . $this->db->escape($transaction['call_data']) . "'
      WHERE chip_order_transaction_id = " . (int)$transaction['chip_order_transaction_id'] . "
    ");
    */
  }

  private function getTransactions($chip_order_id) {
    /*
    $qry = $this->db->query("SELECT `ot`.*, (SELECT count(`ot2`.`chip_order_id`) FROM `" . DB_PREFIX . "chip_order_transaction` `ot2` WHERE `ot2`.`parent_transaction_id` = `ot`.`transaction_id` ) AS `children` FROM `" . DB_PREFIX . "chip_order_transaction` `ot` WHERE `chip_order_id` = '" . (int)$chip_order_id . "'");

    if ($qry->num_rows) {
      return $qry->rows;
    } else {
      return false;
    }
    */
  }

  public function getLocalTransaction($transaction_id) {
    /*
    $result = $this->db->query("
      SELECT *
      FROM " . DB_PREFIX . "chip_order_transaction
      WHERE transaction_id = '" . $this->db->escape($transaction_id) . "'
    ")->row;

    if ($result) {
      return $result;
    } else {
      return false;
    }
    */
  }

  public function getTransaction($transaction_id) {
    /* call API */
  }

  public function getOrderId($transaction_id) {
    /*
    $qry = $this->db->query("SELECT `o`.`order_id` FROM `" . DB_PREFIX . "chip_order_transaction` `ot` LEFT JOIN `" . DB_PREFIX . "chip_order` `o`  ON `o`.`chip_order_id` = `ot`.`chip_order_id`  WHERE `ot`.`transaction_id` = '" . $this->db->escape($transaction_id) . "' LIMIT 1");

    if ($qry->num_rows) {
      return $qry->row['order_id'];
    } else {
      return false;
    }
    */
  }

  public function recurringCancel($ref) {
    /* call API */
  }

  public function set_keys($private_key, $brand_id) {
    $this->private_key = $private_key;
    $this->brand_id    = $brand_id;
  }

  public function get_public_key()
  {
    $result = $this->call('GET', "/public_key/");

    return $result;
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