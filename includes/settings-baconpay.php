<?php
/**
 * Settings for BaconPay Gateway.
 *
 * @package WooCommerce/Classes/Payment
 */

defined( 'ABSPATH' ) || exit;

return array(
  'enabled' => array(
    'title' => __( 'Enable/Disable', 'woocommerce' ),
    'type' => 'checkbox',
    'label' => __( '啟用', 'woocommerce' ),
    'default' => 'no'
  ),
  'title' => array(
    'title' => __( 'Title', 'woocommerce' ),
    'type' => 'text',
    'description' => __( '使用者結帳所顯示的支付名稱', 'woocommerce' ),
    'default' => __( '培根支付', 'baconpay' )
  ),
  'description' => array(
    'title' => __( 'Description', 'woocommerce' ),
    'type' => 'textarea',
    'css' => 'width: 400px;',
    'description' => __( '透過培根支付付款。', 'baconpay' ),
    'default' => __("透過培根支付付款，備註請在下方留言", 'baconpay')
  ),
  'hide_text_box' => array(
    'title' => __( '隱藏付款備註', 'woocommerce' ),
    'type' => 'checkbox',
    'label' => __( '隱藏', 'woocommerce' ),
    'default' => 'no',
    'description' => __( '如果您根本不需要顯示客戶的文本框，請啟用此選項。', 'baconpay' ),
  ),
  'tokenbacon_wallet_address' => array(
    'title' => __( '收款地址', 'baconpay' ),
    'type' => 'text',
    'description' => __( '你收款的培根地址，請注意您的大小寫', 'baconpay' ),
    'default' => 'XkmN3rskt1g55zb5hTNzpwuJLrt3Jx6z9s',
  ),
  'tokenbacon_app_id' => array(
    'title' => __( '申請的ID', 'baconpay' ),
    'type' => 'text',
    'description' => __( '透過<a href="https://tokenbacon.com/wallet" title="另開視窗查看培根地址" target="_blank">培根錢包</a>或<a href="https://tokenbacon.com/wallet" title="另開視窗查看培根地址" target="_blank">培根支付</a>，申請的店家 ID', 'baconpay' ),
  ),
  'tokenbacon_app_secret' => array(
    'title' => __( '密鑰', 'baconpay' ),
    'type' => 'password',
    'description' => __( '透過<a href="https://tokenbacon.com/wallet" title="另開視窗查看培根地址" target="_blank">培根錢包</a>或<a href="https://tokenbacon.com/wallet" title="另開視窗查看培根地址" target="_blank">培根支付</a>，申請的店家密鑰', 'baconpay' ),
  ),
  'tokenbacon_accept_token' => array(
    'title' => __( '接受代幣', 'baconpay' ),
    'type' => 'select',
    'description' => __( '只接受有在培根錢包上架的幣短名稱，第三方代幣可在 <a href="https://tokensnap.io" title="另開視窗查看" target="_blank">TokenSnap</a> 查看（預設值：BaconCoin）', 'baconpay' ),
    'default' => 'BAK.c',
    'options' => array(
      'AMA.JP	' => 'JP.Amazon.eCard',
      'AMA.US	' => 'US.Amazon.eCard',
      'BAK.c' => 'BaconCoin',
      'BTC.b' => 'Bitcoin',
      'DOGE.b' => 'Dogecoin',
      'ETH.b' => 'Ethereum',
      'EFC' => 'ExFamicom',
      'MONA.b' => 'MonaCoin',
      'NANJ.b' => 'NANJCOIN',
      'GIM.b' => 'Gimli',
      )
  ),
  'tokenbacon_fixed_value' => array(
    'title' => __( '代幣價值', 'baconpay' ),
    'type' => 'text',
    'description' => __( '等值該平台預設支付幣別的價格做換算，如：30 NTD 可換成 1 個接收代幣（注意：循環小數最小到八位，<a href="https://baconex.com" title="另開視窗查看 - Bacon Exchange" target="_blank">價值參考</a>，）', 'baconpay' ),
    'default' => '30',
  ),
  'tokenbacon_proportion_value' => array(
    'title' => __( '代幣支付折扣', 'baconpay' ),
    'type' => 'select',
    'description' => __( '使用代幣支付結帳金額折扣優惠，(總金額×折扣%)÷代幣價值', 'baconpay' ),
    'default' => '95',
    'options' => array(
      '10' => '10%',
      '20' => '20%',
      '30' => '30%',
      '40' => '40%',
      '50' => '50%',
      '60' => '60%',
      '65' => '65%',
      '70' => '70%',
      '75' => '75%',
      '80' => '80%',
      '85' => '85%',
      '90' => '90%',
      '95' => '95%',
      '100' => '無折扣',
      )
  ),
  'tokenbacon_cancel_url' => array(
    'title' => __( '取消連結', 'baconpay' ),
    'type' => 'text',
    'description' => __( '取消結帳轉跳位址', 'baconpay' ),
    'default' => 'http://'.$_SERVER['HTTP_HOST'].'/cart'
  ),
  'tokenbacon_redirect_url' => array(
    'title' => __( '結帳連結', 'baconpay' ),
    'type' => 'text',
    'description' => __( '結帳完成後轉跳網址', 'baconpay' ),
    'default' => 'http://'.$_SERVER['HTTP_HOST'].'/checkout/order-received'
  )
);