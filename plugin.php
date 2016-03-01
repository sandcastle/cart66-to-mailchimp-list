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
 * Proceses an order.
 */
function process_order($order_id) {

  // Make sure the Cart66 class exists before attempting to use it
  if(!class_exists('CC')) {
    debug_to_console('CC not defined');
    return;
  }

  // Get the order data
  $order_data = CC::order_data($order_id);

  //do magic here
  look_at_order($order_data);
}

/**
 * Proceses an order.
 */
function look_at_order($order) {

  $url = 'http://requestb.in/1nhnycj1';

  // serialize the order to JSON
  $data = json_encode($order);

  debug_to_console($data);

  $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => $data,
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
  );

  $context  = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );

  if ($result === FALSE) {
    debug_to_console('Failed to post to URL');
  }

  $response = json_decode( $result );
}

function add_to_cart($val) {
  debug_to_console('Added item to cart');
  debug_to_console($val);
}

add_action('cart66_after_order_saved',  'add_to_cart', 'cart66_after_order_saved');
add_action('cart66_after_remove_item',  'add_to_cart', 'cart66_after_remove_item');
add_action('cart66_after_add_to_cart',  'add_to_cart', 'cart66_after_add_to_cart');
add_action('cart66_before_add_to_cart', 'add_to_cart', 'cart66_before_add_to_cart');
add_action('cart66_after_update_car',   'add_to_cart', 'cart66_after_update_car');
add_action('cc_load_receipt', 'process_order');

?>
