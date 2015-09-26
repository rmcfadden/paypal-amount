<?php
/**
* Plugin Name: Paypal Amount
* Plugin URI: https://github.com/rmcfadden/paypal-amount
* Description: A brief description about your plugin.
* Version: 1.0
* Author: Ryan McFadden
* Author URI: https://github.com/rmcfadden
* License: GPLv2
*/


add_action('plugins_loaded', array( 'paypalAmount', 'init' ));
register_activation_hook(__FILE__, array('paypalAmount',  'activation' ));

class paypalAmount {

    private $options;
    private static $page_name = 'paypal_amount';
    private static $options_name = 'paypal_amount_options';

    // https://developer.paypal.com/docs/classic/api/buttons/
    private static $paypal_buttons = array(
        1 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_pponly_142x27.png"),    
        2 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png"),
        3 => array("large", "https://www.paypalobjects.com/en_US/i/btn/x-click-but6.gif"),
        4 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_addtocart_96x21.png"),
        5 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_addtocart_120x26.png"),
        6 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_86x21.png"),
        7 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_107x26.png"),
        8 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_cc_171x47.png"),
        9 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_pp_142x27.png"),
        10 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_74x21.png"),
        11 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_92x26.png"),
        12 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_cc_147x47.png"),
        13 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png"),
        14 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_86x21.png"),
        15 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_107x26.png"),
        16 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_cc_144x47.png"),
        17 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_91x21.png"),
        18 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_113x26.png"),
        19 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_cc_147x47.png"),
        20 => array("small", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_37x23.png"),
        21 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_74x46.png"),
        22 => array("large", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_111x69.png"),
        23 => array("extralarge", "https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg"),
        24 => array("extralarge", "https://www.paypalobjects.com/webstatic/mktg/logo/bdg_now_accepting_pp_2line_w.png")
    );

    public static function init() {
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
    }

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

        $target = 'paypal';
        if(isset($options['target'])){
            $target = $options['target'];
        }

	    return '<form   action="https://www.paypal.com/cgi-bin/webscr" method="post">
    			<div class="paypal_amount">
                    <input type="hidden" name="cmd" value="_xclick">
			        <input type="hidden" name="business" value="' . $paypal_id . '">
			        <input type="text" name="amount">
			        <input type="image" src="'. $image_url . '" name="submit">
                    <input type="hidden" name="currency_code" value="USD">
			    </div>
			</form>';
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

        wp_enqueue_script('paypal-amount.js', plugin_dir_url(__FILE__) . 'paypal-amount.js', array('jquery'));

        register_setting(
            paypalAmount::$options_name,
            paypalAmount::$options_name,
            array( $this, 'sanitize' )
        );

        $section_name = paypalAmount::$options_name + '_section';

        add_settings_section(
            $section_name,
            'Change your settings below:',
            array($this, 'options_callback'),
            paypalAmount::$page_name
        );

	    add_settings_field(
		    'paypal_id', 
            'PayPal id:', 
		    array($this,'paypal_id_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'paypal_id' )
	    );

	    add_settings_field(
		    'button_type', 
            'Button type:', 
		    array($this,'paypal_button_type_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'button_type' )
	    );


	    add_settings_field(
		    'button_size', 
            'Button size:', 
		    array($this,'paypal_button_size_callback'), 
		    paypalAmount::$page_name, 
            $section_name,
		    array( 'label_for' => 'button_size' )
	    );


	    add_settings_field(
		    'button_id', 
            'Choose a button:', 
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
		        <label class="paypal-amount-button-label" data-button-size='<?= $size ?>' >
			        <input type='radio' name='<?= $current_options_name ?>[button_id]' value='<?= $id ?>' <?= $is_checked ?>>
			        <img src='<?= $url ?>' style='vertical-align: middle; margin-left: 15px;'>
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
    