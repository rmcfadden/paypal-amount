<?php
/**
* Plugin Name: Paypal Amount
* Plugin URI: https://github.com/rmcfadden/paypal-amount
* Description: The easiest way to add a paypal button to your wordpress site.  Includes support for entering variable payment amounts!
* Version: 1.0
* Author: Ryan McFadden
* Author URI: https://github.com/rmcfadden
* License: GPLv2
*/


add_action('plugins_loaded', array( 'paypalAmount', 'load' ));
register_activation_hook(__FILE__, array('paypalAmount',  'activation' ));

class paypalAmount {

    private $options;
    private static $page_name = 'paypal_amount';
    private static $options_name = 'paypal_amount_options';

    // https://developer.paypal.com/docs/classic/api/buttons/
    private static $paypal_buttons = array(
        1 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_pponly_142x27.png", array("all")),    
        2 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png",array("checkout")),
        3 => array("large", "https://www.paypalobjects.com/en_US/i/btn/x-click-but6.gif",array("buynow")),
        4 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_addtocart_96x21.png",array("addtocart")),
        5 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_addtocart_120x26.png",array("addtocart")),
        6 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_86x21.png",array("buynow")),
        7 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_107x26.png",array("buynow")),
        8 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_cc_171x47.png",array("buynow")),
        9 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_pp_142x27.png",array("buynow")),
        10 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_74x21.png",array("donate")),
        11 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_92x26.png",array("donate")),
        12 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_cc_147x47.png",array("donate")),
        13 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png",array("donate")),
        14 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_86x21.png",array("buynow")),
        15 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_107x26.png",array("buynow")),
        16 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_cc_144x47.png",array("buynow")),
        17 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_91x21.png",array("subscribe")),
        18 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_113x26.png",array("subscribe")),
        19 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_cc_147x47.png",array("subscribe")),
        20 => array("small", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_37x23.png",array("buynow")),
        21 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_74x46.png",array("buynow")),
        22 => array("large", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_111x69.png",array("buynow")),
        23 => array("extralarge", "https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg",array("buynow")),
        24 => array("extralarge", "https://www.paypalobjects.com/webstatic/mktg/logo/bdg_now_accepting_pp_2line_w.png",array("buynow"))
    );

    public static function load() {
        $class = __CLASS__;
        new $class;
    }

    public static function activation() {
        $new_options = array(
            'paypal_id' => -1,
            'button_id' => 0,
            'button_size' => 'small',
            'button_type' => 'buynow',
            'target' => 'paypal'
        );

	    if ( get_option(paypalAmount::$options_name ) !== false ) {
      	    update_option(paypalAmount::$options_name, $new_options );
        } 
        else{
   		    add_option(paypalAmount::$options_name, $new_options );
        }

    }

    public function __construct() { 
        add_shortcode( 'paypal_amount', array( $this, 'shortcode' )); 
        add_action('admin_menu', array( $this, 'admin_option_init'));
        add_action('admin_init', array( $this, 'admin_init' ));
        add_action('init', array( $this, 'init' ));

    }

    // Good refeence for paypal button code: http://planetoftheweb.com/components/promos.php?id=542
    public function shortcode() 
    {
        $options = get_option( paypalAmount::$options_name );

        $image_url = 'https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif';
        
        if(isset($options['button_id'])){ 
            $button_id = $options['button_id'];

            if(isset(paypalAmount::$paypal_buttons[$button_id])){
                $image_url = paypalAmount::$paypal_buttons[$button_id][1];
                
            }
        }

        $paypal_id = -1;
        if(isset($options['paypal_id'])){
            $paypal_id = $options['paypal_id'];
        }

        $paypal_type = 'buynow';
        if(isset($options['paypal_type'])){
            $paypal_type = $options['paypal_type'];
        }


        $cmd_value = '_xclick';
        if($paypal_type == "donate"){
            $cmd_value = "_donations";
        }


        $target = 'paypal';
        if(isset($options['target'])){
            $target = $options['target'];
        }

	    return '<form   action="https://www.paypal.com/cgi-bin/webscr" method="post">
    			<div class="paypal_amount">
                    <input type="hidden" name="cmd" value="_xclick">
			        <input type="hidden" name="business" value="' . $paypal_id . '">
			        <input type="text" name="amount" onkeyup="checkDecimal(this)" value="0.00" >
			        <input type="image" src="'. $image_url . '" name="submit">
                    <input type="hidden" name="currency_code" value="USD">
			    </div>
			</form>';
    }

    public function init() {
        wp_enqueue_script('paypal-amount.js', plugin_dir_url(__FILE__) . 'paypal-amount.js', array('jquery'));  
    }


    public function admin_option_init() {
        add_options_page('PayPal Amount', 'PayPal Amount', 'manage_options', paypalAmount::$page_name, array( $this, 'admin_options_page' ));
    }

    public function admin_options_page() {
        $this->options = get_option( paypalAmount::$options_name );

        ?>
        <div class="wrap">
            <h2>PayPal Amount</h2>           
            <form method="post" action="options.php">
            <?php
                settings_fields(paypalAmount::$options_name );   
                do_settings_sections( paypalAmount::$page_name );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    public function admin_init() {

        wp_enqueue_script('paypal-amount-admin.js', plugin_dir_url(__FILE__) . 'paypal-amount-admin.js', array('jquery'));

        register_setting(
            paypalAmount::$options_name,
            paypalAmount::$options_name,
            array( $this, 'sanitize' )
        );

        $section_name = paypalAmount::$options_name + '_section';

        add_settings_section(
            $section_name,
            __('Change your settings below.  Don\'t forget to hit \'Save Changes!\' to apply!'),
                array($this, 'options_callback'),
            paypalAmount::$page_name
        );

	    add_settings_field(
		    'paypal_id', 
            __('PayPal id:'), 
		    array($this,'paypal_id_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'paypal_id' )
	    );

	    add_settings_field(
		    'button_type', 
            __('Button type:'), 
		    array($this,'paypal_button_type_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'button_type' )
	    );


	    add_settings_field(
		    'button_size', 
            __('Button size:'), 
		    array($this,'paypal_button_size_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'button_size' )
	    );


	    add_settings_field(
		    'button_id', 
            __('Choose a button:'), 
		    array($this,'paypal_button_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'button_id' )
	    );
    }


    function paypal_id_callback() {
        $options = get_option(paypalAmount::$options_name);
        $current_options_name = paypalAmount::$options_name;

	    echo "<input class='regular-text ltr' name='{$current_options_name}[paypal_id]' id='paypal_id'  value='{$options['paypal_id']}'/>";
    }



    function paypal_button_size_callback() {

        $options = get_option(paypalAmount::$options_name);
        $current_options_name = paypalAmount::$options_name;

        $button_size = 'medium';
        if(isset($options['button_size'])){
            $button_size = $options['button_size'];    
        }

	    ?>
	    <select id='paypal_amount_button_size' name='<?= $current_options_name ?>[button_size]'>
            <option value='small' <?php if($button_size == 'small') { echo 'selected'; }  ?>>Small</option>
            <option value='medium' <?php if($button_size == 'medium') { echo 'selected'; }  ?>>Medium</option>
            <option value='large' <?php if($button_size == 'large') { echo 'selected'; }  ?>>Large</option>
            <option value='extralarge' <?php if($button_size == 'extralarge') { echo 'selected'; }  ?>>Extra Large</option>
	    </select>
	    <?php          
        
    }


    function paypal_button_type_callback(){
        $options = get_option(paypalAmount::$options_name);
        $current_options_name = paypalAmount::$options_name;        

        $button_type = 'buynow';
        if(isset($options['button_type'])){
            $button_type = $options['button_type'];    
        }

	    ?>
	    <select id='paypal_amount_button_type' name='<?= $current_options_name ?>[button_type]'>
            <option value='buynow' <?php if($button_type == 'buynow') { echo 'selected'; }  ?>>Buy Now</option>
            <option value='donate' <?php if($button_type == 'donate') { echo 'selected'; }  ?>>Donate</option>
	    </select>
	    <?php          
        
    }


    function paypal_button_callback(){

        $options = get_option(paypalAmount::$options_name);
        $current_options_name = paypalAmount::$options_name;

		foreach(paypalAmount::$paypal_buttons as $id => $button_info) :

            $size = $button_info[0];
            $url = $button_info[1];


            $type = 'buynow';
            if(isset($button_info[2]))
            {
                $type = implode($button_info[2]);          
            }

            $button_id = -1;
            if(isset($options['button_id'])){ 
                $button_id = $options['button_id'];
            }

            $is_checked = '';
            if($button_id == $id){
                $is_checked = 'checked';    
            }


            ?>
	        <p>
		        <label class="paypal-amount-button-label" data-button-size='<?= $size ?>' data-button-type='<?= $type ?>' >
			        <input type='radio' name='<?= $current_options_name ?>[button_id]' value='<?= $id ?>' <?= $is_checked ?>>
			        <img src='<?= $url ?>' style='vertical-align: middle; margin: 10px;'>
		        </label>
	        </p>
        	<?php          

		endforeach;	
    }


    function options_callback() {
    }

    public function sanitize( $input ){

        return $input;
    }
}
    