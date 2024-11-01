<?php
/**
 * Class WC_Gateway_Baconpay_Request file.
 *
 * @package WooCommerce\Gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WC_Gateway_Baconpay_Request {
  /**
   * Pointer to gateway making the request.
   *
   * @var WC_Gateway_Baconpay
   */
  protected $gateway;

  /**
   * Endpoint for requests from Baconpay.
   *
   * @var string
   */
  protected $notify_url;

  /**
   * Endpoint for requests to Baconpay.
   *
   * @var string
   */
  protected $endpoint;
  
  /**
   * Endpoint for requests to Baconpay.
   *
   * @var string
   */
  protected $access_token;

  /**
   * 
   * @var string
   */
  protected $order_token;

  /**
   * Constructor.
   *
   * @param WC_Gateway_Baconpay $gateway Baconpay gateway object.
   */
  public function __construct( $gateway ) {
    $this->gateway = $gateway;
    $this->notify_url = WC()->api_request_url( 'WC_Gateway_Baconpay' );
    $tokenbacon_app_id = $this->gateway->get_option('tokenbacon_app_id');
    $tokenbacon_app_secret = $this->gateway->get_option('tokenbacon_app_secret');
    $this->access_token = $this->get_transaction_token($tokenbacon_app_id, $tokenbacon_app_secret);
  }

  // Authentication 身份驗證 並 申請 token，相容於 v2
  private function get_transaction_token($app_id, $secret) {
    $request_transaction_token = wp_remote_post('https://tokenbacon.com/payment/token', array(
      'data_format' => 'x-www-form-urlencoded',
      'headers' => array(
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Basic '.base64_encode($app_id.':'.$secret),
      ),
      'body' => array( 'grant_type' => 'client_credentials'),
      'sslverify' => false,
    ));
    $request_transaction_token_format = json_decode($request_transaction_token['body']);
    if ($request_transaction_token_format->result === 'success') {
      return $request_transaction_token_format->access_token;
    }
    return $request_transaction_token_format->message;
  }

  /**
   * 整理定單資料.
   *
   * @param WC_Order $order Order object.
   * @return array
   */
  protected function get_transaction_args( $order ) {
    // 跑回圈整理格式
    $bacon_items_format = $this->get_woocommerce_cart_item_format($order->get_order_number());

    $this->storeOauthData(
      $order->get_order_number(),
      $this->order_token,
      $order->get_billing_email(),
      $this->gateway->get_option('tokenbacon_accept_token'),
      $bacon_items_format['total'],
      $this->gateway->get_option('tokenbacon_wallet_address')
    );
    if ($this->gateway->get_option('tokenbacon_proportion_value') !== 100) {
      return array (
        'redirect_url' => 'http://'.$_SERVER['HTTP_HOST'].'/wc-api/baconpay_checkout',
        'cancel_url' => $this->gateway->get_option('tokenbacon_cancel_url'),
        'items' => $bacon_items_format['items'],
        'accept_token' => $this->gateway->get_option('tokenbacon_accept_token'),
        'amount' => $bacon_items_format['total'],
        'order_id' => $order->get_order_number(),
        'receiver' => $this->gateway->get_option('tokenbacon_wallet_address'),
      );
    } else {
      return array (
        'redirect_url' => $this->gateway->get_option('tokenbacon_redirect_url'),
        'cancel_url' => $this->gateway->get_option('tokenbacon_cancel_url'),
        'items' => $bacon_items_format['items'],
        'accept_token' => $this->gateway->get_option('tokenbacon_accept_token'),
        'amount' => $bacon_items_format['total'],
        'order_id' => $order->get_order_number(),
        'receiver' => $this->gateway->get_option('tokenbacon_wallet_address'),
      );
    }
  }

  /**
   * Get woocommerce 購物車清單 整理後的格式 相容第二版
   *
   * @param  WC_Order $order Order object.
   * @return array
   */
  protected function get_woocommerce_cart_item_format($order_id) {
    $total_rate = 0;
    $items_format = array();
    $order = wc_get_order($order_id);

    foreach ($order->get_items() as $item_id => $item_data) {
      $product = $item_data->get_product();
      $product_name = $product->get_name();         // Get the product name
      $item_quantity = $item_data->get_quantity();  // Get the item quantity
      $item_total = $item_data->get_total();        // Get the item line total
      $item_image_html = $product->get_image();     // Get the item line image html tage

      // 判斷有沒有圖片字串做切割
      if (strpos($item_image_html, 'src=') === false || 'v2API' === 'v2API') {
        $item_total_rate = round(($item_total / $this->gateway->get_option('tokenbacon_fixed_value'))*($this->gateway->get_option('tokenbacon_proportion_value')/100), 8);
        array_push($items_format,
          array(
            'name' => $product_name,
            'quantity' => $item_quantity,
            'amount' => $item_total_rate
          )
        );
      } else {
        $item_total_rate = round(($item_total / $this->gateway->get_option('tokenbacon_fixed_value'))*($this->gateway->get_option('tokenbacon_proportion_value')/100), 8);
        array_push($items_format,
          array(
            'img' => explode('"', substr($item_image_html ,strpos($item_image_html, 'src=')))[1],
            'name' => $product_name,
            'quantity' => $item_quantity,
            'amount' => $item_total_rate
            )
        );
      }
      $total_rate += $item_total_rate;
    }
    return array('items' => $items_format, 'total' => $total_rate);
  }

  /**
   * Get the Baconpay 付款頁面連結
   *
   * @param  WC_Order $order Order object.
   * @return string
   */
  public function get_request_url( $order ) {
    // 打 Token 付款
    $request_checkout = wp_remote_post( 'https://tokenbacon.com/payment/create', array(
      'data_format' => 'x-www-form-urlencoded',
      'headers' => array(
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => 'Bearer '.$this->access_token
      ),
      'body' => json_encode($this->get_transaction_args($order), JSON_UNESCAPED_UNICODE),
      'sslverify' => false,
      )
    );
    $request_checkout_format=json_decode($request_checkout['body']);
    if($request_checkout_format->result === 'success'){
      $this->endpoint = $request_checkout_format->link;
      $this->order_token = $request_checkout_format->order_token;
    }
    return $this->endpoint;
  }

  // 結帳玩寫入資料庫
  private function storeOauthData($order_id, $order_token, $email, $accept_token_symbol, $amount, $receiver_addr) {
    global $wpdb;
    $table_name = $wpdb->prefix.'tokenbacon_order';
    $wpdb->insert($table_name, array(
      'order_id' => $order_id,
      'order_token' => $order_token,
      'email' => $email,
      'accept_token_symbol' => $accept_token_symbol,
      // 'accept_token_id' => $accept_token_id,
      'amount' => $amount,
      'receiver_addr' => $receiver_addr,
    ));
  }
}