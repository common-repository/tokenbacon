<?php
/**
 * BaconPay
 *
 * @package     BaconPay
 * @author      TokenBacon Taiwan Co., Ltd. <support@tokenbacon.com>
 * @copyright   2019 TokenBacon Taiwan Co., Ltd.
 * @license     GPLv3
 *
 * Plugin Name: BaconPay
 * Plugin URI: http://baconpay.io
 * Description: 在 WooCommerce 接受加密貨幣在線支付，代幣化您的商品與點數，並參與最流行的加密貨幣交易。
 * Version: 0.0.1
 * Author: TokenBacon <support@tokenbacon.com>
 * Author URI: https://tokenbacon.com
 * Text Domain: baconpay
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || die();

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if(BaconPay::baconpay_payment_is_woocommerce_active()){
  
  add_filter('woocommerce_payment_gateways', 'woocommerce_add_baconpay_payment_gateway');
  function woocommerce_add_baconpay_payment_gateway( $gateways ) {
    $gateways[] = 'WC_BaconPay_Payment_Gateway';
    return $gateways;
  }

  add_action('plugins_loaded', 'woocommerce_init_baconpay_payment_gateway');
  function woocommerce_init_baconpay_payment_gateway() {
    require 'includes/class-wc-gateway-baconpay.php';
  }

  add_action( 'plugins_loaded', 'load_baconpay_textdomain' );
  function load_baconpay_textdomain() {
    load_plugin_textdomain('includes/class-wc-gateway-baconpay', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
  }
}

class BaconPay
{
  function activate() {
    $this->tokenbacon_install();
    if($this->baconpay_payment_is_woocommerce_active()){
      flush_rewrite_rules();
    }
  }

  function deactivate() {

  }

  function uninstall() {

  }

  function tokenbacon_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "tokenbacon_order";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
      id mediumint NOT NULL AUTO_INCREMENT,
      order_id text NOT NULL,
      order_token text NOT NULL,
      email VARCHAR(50) NOT NULL,
      accept_token_symbol VARCHAR(50) NOT NULL,
      accept_token_id VARCHAR(50),
      amount VARCHAR(50) NOT NULL,
      receiver_addr VARCHAR(50) NOT NULL,
      pay_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) $charset_collate;";
    require_once( ABSPATH.'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  /**
  * @return bool
  * Determine if there is an installation woocommerce
  */
  function baconpay_payment_is_woocommerce_active()
  {
    $active_plugins = (array) get_option('active_plugins', array());

    if (is_multisite()) {
      $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }

    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
  }
}

if (class_exists('BaconPay')) {
  $tokenBaconPlugin = new BaconPay();
}

register_activation_hook( __FILE__, array( $tokenBaconPlugin, 'activate') );
register_deactivation_hook( __FILE__, array( $tokenBaconPlugin, 'deactivate') );
