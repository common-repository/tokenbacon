<?php 
/**
 * Class WC_BaconPay_Payment_Gateway file.
 *
 * @package WooCommerce\Gateways
 */
class WC_BaconPay_Payment_Gateway extends WC_Payment_Gateway {

  public function __construct(){
    $this->id = 'baconpay';
    $this->icon = plugin_dir_url( __FILE__ ).'../assets/images/baconpay.png';
    $this->has_fields = true;
    $this->enabled = $this->get_option('enabled');
    $this->method_title = __('培根支付', 'baconpay');
    $this->method_description = __('培根支付網站付款標準版會將顧客重新導向至培根支付，讓他們在那裡輸入自己的付款資訊。','baconpay');

    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    $this->title = $this->get_option('title').' <a href="http://baconpay.io" target="_blank">什麼是培根支付?</a>';
    $this->description = $this->get_option('description');
    $this->instructions = $this->get_option('instructions');

    // 送單的備註
    $this->hide_text_box = $this->get_option('hide_text_box');
    add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));

    // --- 分割線 ---
    // 結帳完回傳
    add_action( 'woocommerce_api_baconpay_checkout', array( $this, 'get_baconpay_checkout'));
  }

  /**
  * Initialise Gateway Settings Form Fields，後臺表格設定
  */
  public function init_form_fields() {
    $this->form_fields = include 'settings-baconpay.php';
  }

  public function get_baconpay_checkout() {
    if(isset($_GET['order_token']) && isset($_GET['order_id'])) {
      wp_safe_redirect(wc_get_checkout_url());
      exit;
    }
  }
  /**
   * Process the payment and return the result.
   *
   * @param  int $order_id Order ID.
   * @return array
   */
  public function process_payment( $order_id ) {
    global $woocommerce;
    // Reduce stock levels 降低庫存
    wc_reduce_stock_levels( $order_id );
    if(isset($_POST[ $this->id.'-admin-note']) && trim($_POST[ $this->id.'-admin-note'])!=''){
      $order->add_order_note(esc_html($_POST[ $this->id.'-admin-note']),1);
    }

    include_once dirname( __FILE__ ) . '/class-wc-gateway-baconpay-request.php';
    $order = wc_get_order( $order_id );
    // 付款程式進入點
    $baconpay_request = new WC_Gateway_Baconpay_Request( $this );

    // 100% 完全支付 Remove cart 清空購物車
    if($this->get_option('tokenbacon_proportion_value') === 100) {
      $woocommerce->cart->empty_cart();
    }

    // Return thankyou redirect 轉跳到培根支付頁面
    return array(
      'result' => 'success',
      'redirect' => $baconpay_request->get_request_url( $order ),
    );
  }

  // 用戶 附註 輸入框
  public function payment_fields(){
    if($this->hide_text_box !== 'yes'){
    ?>
    <fieldset>
      <p class="form-row form-row-wide">
        <label for="<?php echo $this->id; ?>-admin-note"><?php echo ($this->description); ?></label>
        <textarea id="<?php echo $this->id; ?>-admin-note" class="input-text" type="text" name="<?php echo $this->id; ?>-admin-note"></textarea>
      </p>
      <div class="clear"></div>
    </fieldset>
    <?php
    }
  }
}