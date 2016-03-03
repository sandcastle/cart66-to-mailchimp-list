<?php
/*
Plugin Name: Cart66 to Mailchimp List
Plugin URI: https://github.com/sandcastle/cart66-to-mailchimp-list
Description: Adds a user to a mailchimp account.
Author: Sandcastle
Version: 0.9
Author URI: https://github.com/sandcastle
*/


/**
 * Print a debug message to the browser config using "console.log"
 */
function debug_to_console($data) {

    if (is_array($data)) {
        $output = "<script>console.log('".implode(',', $data)."' );</script>";
    } else {
        $output = "<script>console.log('".$data."' );</script>";
    }

    echo $output;
}

/**
 * Get the current time in milliseconds for accuracy.
 */
function get_milli_time() {
    $t = microtime(true);
    $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
    $d = new DateTime(date('Y-m-d H:i:s.'.$micro, $t));
    return $d->format("Y-m-d H:i:s.u");
}

/**
 * Proceses an order.
 */
function process_order($order_id) {

    // Make sure the Cart66 class exists before attempting to use it
    if (!class_exists('CC')) {
        debug_to_console('CC not defined');
        return;
    }

    // Get the order data
    $order = CC::order_data($order_id);

    // send_order($order);
    subscribe_user($order);
}

/**
 * Proceses an order.
 */
function subscribe_user($order) {

  // ------

  $list_id = '';
  $apikey = '';
  $dc = substr($apikey, strripos($apikey, '-') + 1);

  // ------

  // Build request
  $data = array(
    'apikey' => $apikey,
    'email_address' => $order["contact"]["email"],
    'status' => 'subscribed',
    'merge_fields' => array(
      'FNAME' => $order["contact"]["first_name"],
      'LNAME' => $order["contact"]["last_name"]
    )
  );

  // Serialize the order to JSON
  $jsonData = json_encode($data);

  // ------

  $auth = base64_encode('user:'.$apikey);
  $url = 'https://'.$dc.'.api.mailchimp.com/3.0/lists/'.$list_id.'/members';

  $headr = array(
    'Content-Type: application/json',
    'Authorization: Basic '.$auth
  );

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_POST, true);
  curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);
  curl_setopt($handle, CURLOPT_HTTPHEADER, $headr);
  curl_exec($handle);
  if (empty($result)) {
      debug_to_console('error');
  } else {
      debug_to_console('success');
  }
  curl_close($handle);
}

// add_action('cc_load_receipt', 'process_order');

// ------

// TODO: Remove test trigger

$testData = array(
  'contact' => array(
    'email' => 'glenn@glennandkristy.com',
    'first_name' => 'Glenn',
    'last_name' => 'Morton'
  )
);

subscribe_user($testData);

// ------

?>
