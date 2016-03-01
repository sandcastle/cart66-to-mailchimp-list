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
function debug_to_console( $data ) {

  if ( is_array( $data ) ) {
      $output = "<script>console.log('" . implode( ',', $data) . "' );</script>";
  }
  else {
      $output = "<script>console.log('" . $data . "' );</script>";
  }

  // Print to the page
  echo $output;
}

/**
 * Get the current time in milliseconds for accuracy.
 */
function get_milli_time() {
  $t = microtime(true);
  $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
  $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
  return $d->format("Y-m-d H:i:s.u");
}

/**
 * Proceses an order.
 */
function process_order($order_id) {

  // Make sure the Cart66 class exists before attempting to use it
  if(!class_exists('CC')) {
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

  $list_id = '';
  $apikey = '';
  $dc = substr($apikey, strripos($apikey, '-') + 1);

  // ------

  // TODO: Remove debug information

  // Log the current time
  $time = get_milli_time();
  echo "Now: " . $time;

  $jsonOrder = json_encode($order);
  echo $jsonOrder;

  // ------

  // Build request
  $data = array(
    'apikey'        => $apikey,
    'email_address' => $order.contact.email,
    'status'        => 'subscribed',
    'merge_fields'  => array(
        'FNAME' => $order.contact.first_name,
        'LNAME' => $order.contact.last_name
    )
  );

  // Serialize the order to JSON
  $jsonData = json_encode($data);

  debug_to_console($jsonData);

  // ------

  $auth = base64_encode( 'user:' . $apikey );
  $url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members';
  $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => $jsonData,
      'header'=>
        'Content-Type: application/json\r\n' .
        'Accept: application/json\r\n' .
        'Authorization: Basic '.$auth
      )
  );

  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);

  if ($result === FALSE) {
    debug_to_console('Failed to post to URL');
  }

  $response = json_decode( $result );

  debug_to_console('Response');
  debug_to_console($response);
}


// /**
//  * Proceses an order.
//  */
// function send_order($order) {

//   $url = 'http://requestb.in/1nhnycj1';

//   // serialize the order to JSON
//   $data = json_encode($order);

//   $options = array(
//     'http' => array(
//       'method'  => 'POST',
//       'content' => $data,
//       'header'=>  "Content-Type: application/json\r\n" .
//                   "Accept: application/json\r\n"
//       )
//   );

//   $context  = stream_context_create( $options );
//   $result = file_get_contents( $url, false, $context );

//   if ($result === FALSE) {
//     debug_to_console('Failed to post to URL');
//   }

//   $response = json_decode( $result );
// }

add_action('cc_load_receipt', 'process_order');

?>
