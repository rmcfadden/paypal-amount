<?php
/**
* Plugin Name: Ryan's Payment Button
* Plugin URI: https://github.com/rmcfadden/ryans-payment-button
* Description: Add a paypal button to your wordpress site with an a optional payment amount textbox.  Choose from multiple paypal buttons.
* Version: 1.0
* Author: Ryan McFadden
* Author URI: https://github.com/rmcfadden
* License: GPLv2
*/


add_action('plugins_loaded', array( 'ryansPaymentButton', 'load' ));
register_activation_hook(__FILE__, array('ryansPaymentButton',  'activation' ));

// TODO: shortcodes with parameters
// https://developer.wordpress.org/plugins/shortcodes/shortcodes-with-parameters/

class ryansPaymentButton {

    private $options;
    private static $page_name = 'ryans_payment_button';
    private static $options_name = 'ryans_payment_button_options';
    private $default_amount_description = '';

    // https://developer.paypal.com/docs/classic/api/buttons/
    private static $paypal_buttons = array(
        1 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_pponly_142x27.png", "all"),    
        2 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png","checkout"),
        3 => array("large", "https://www.paypalobjects.com/en_US/i/btn/x-click-but6.gif","buynow"),
        4 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_addtocart_96x21.png","addtocart"),
        5 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_addtocart_120x26.png","addtocart"),
        6 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_86x21.png","buynow"),
        7 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_107x26.png","buynow"),
        8 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_cc_171x47.png","buynow"),
        9 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_buynow_pp_142x27.png","buynow"),
        10 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_74x21.png","donate"),
        11 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_92x26.png","donate"),
        12 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_cc_147x47.png","donate"),
        13 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png","donate"),
        14 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_86x21.png","buynow"),
        15 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_107x26.png","buynow"),
        16 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_paynow_cc_144x47.png","buynow"),
        17 => array("small", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_91x21.png","subscribe"),
        18 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_113x26.png","subscribe"),
        19 => array("large", "https://www.paypalobjects.com/webstatic/en_US/btn/btn_subscribe_cc_147x47.png","subscribe"),
        20 => array("small", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_37x23.png","buynow"),
        21 => array("medium", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_74x46.png","buynow"),
        22 => array("large", "https://www.paypalobjects.com/webstatic/en_US/logo/pp_cc_mark_111x69.png","buynow"),
        23 => array("extralarge", "https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg","buynow"),
        24 => array("extralarge", "https://www.paypalobjects.com/webstatic/mktg/logo/bdg_now_accepting_pp_2line_w.png","buynow")
    );


    public static function load() {
        $class = __CLASS__;
        new $class;
    }


    public static function activation() {
        $new_options = array(
            'paypal_id' => -1,
            'button_id' => 0,
            'size' => 'small',
            'type' => 'buynow',
            'textbox_location' => 'top',
            'amount_description' => $default_amount_description,
            'amount_default' => 20.00,
            'currency' => 'USD'
        );

        if ( get_option(ryansPaymentButton::$options_name ) !== false ) {
            update_option(ryansPaymentButton::$options_name, $new_options );
        } 
        else{
            add_option(ryansPaymentButton::$options_name, $new_options );
        }
    }


    public function __construct() { 
        add_shortcode( 'ryans_payment_button', array( $this, 'shortcode' )); 
        add_action('admin_menu', array( $this, 'admin_option_init'));
        add_action('admin_init', array( $this, 'admin_init' ));
        add_action('init', array( $this, 'init' ));

        $this->default_amount_description  = __('Please enter payment amount and click the button below:');

    }


    // Good refeence for paypal button code: http://planetoftheweb.com/components/promos.php?id=542
    public function shortcode() 
    {
        $options = get_option( ryansPaymentButton::$options_name );

        $image_url = 'https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif';
        $cmd_value = '_xclick';    

        if(isset($options['button_id'])){ 
            $button_id = $options['button_id'];

            if(isset(ryansPaymentButton::$paypal_buttons[$button_id])){
                $image_url = ryansPaymentButton::$paypal_buttons[$button_id][1];
  
                if(isset(ryansPaymentButton::$paypal_buttons[$button_id][2])){
                    $command_value = ryansPaymentButton::$paypal_buttons[$button_id][2];
                    if($command_value == "donate"){
                        $cmd_value = "_donations";
                    }
                }                                        
            }
        }

        $paypal_id = -1;
        if(isset($options['paypal_id'])){
            $paypal_id = $options['paypal_id'];
        }

        $paypal_type = 'buynow';
        if(isset($options['type'])){
            $paypal_type = $options['type'];
        }

        $target = 'paypal';
        if(isset($options['target'])){
            $target = $options['target'];
        }

        $textbox_location = 'top';
        if(isset($options['textbox_location'])){
            $textbox_location = $options['textbox_location'];
        }

        $amount_description = $this->default_amount_description;
        if(isset($options['amount_description'])){
            $amount_description = $options['amount_description'];
        }

        $amount_default = 20.00;
        if(isset($options['amount_default'])){
            $amount_default = $options['amount_default'];
        }

        $currency = 'USD';
        if(isset($options['currency'])){
            $currency = $options['currency'];
        }

        $text_amount_label = "<label>" . $amount_description  . "</label>\n";
        $text_amount_text = '<input type="text" class="ryans-payment-button-textbox" name="amount" onkeyup="ryans_payment_button_check_decimal(this)" value="' . $amount_default . '" >';
        if($textbox_location == 'hidden'){
            $text_amount_label = '';
            $text_amount_text = '';
        }

        return '<form   action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <div class="ryans-payment-button">
                <input type="hidden" name="cmd" value="' . $cmd_value . '">
                <input type="hidden" name="business" value="' . $paypal_id . '">'
                . $text_amount_label 
                . $text_amount_text .
                '<input type="image" src="'. $image_url . '" name="submit">
                <input type="hidden" name="currency_code" value="' . $currency .'">
            </div>
            </form>';
    }


    public function init() {
        wp_enqueue_script('ryans-payment-button.js', plugin_dir_url(__FILE__) . 'ryans-payment-button.js', array('jquery')); 
        wp_enqueue_style( 'ryans-payment-button', plugin_dir_url(__FILE__) . 'ryans-payment-button.css' ); 
    }


    public function admin_option_init() {
        add_options_page('Ryan\'s Payment Button, 'Ryan\'s Payment Button', 'manage_options', ryansPaymentButton::$page_name, array( $this, 'admin_options_page' ));
    }


    public function admin_options_page() {
        $this->options = get_option( ryansPaymentButton::$options_name );

        ?>
        <div class="wrap">
            <h2>PayPal Amount</h2>           
            <form method="post" action="options.php">
            <?php
                settings_fields(ryansPaymentButton::$options_name );   
                do_settings_sections( ryansPaymentButton::$page_name );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }


    public function admin_init() {
        wp_enqueue_script('ryans-payment-button-admin.js', plugin_dir_url(__FILE__) . 'ryans-payment-button-admin.js', array('jquery'));
        wp_enqueue_script('ryans-payment-button.js', plugin_dir_url(__FILE__) . 'ryans-payment-button.js', array('jquery')); 

        register_setting(
            ryansPaymentButton::$options_name,
            ryansPaymentButton::$options_name,
            array( $this, 'sanitize' )
        );

        $section_name = ryansPaymentButton::$options_name + '_section';

        add_settings_section(
            $section_name,
            __('Change your settings below.  Don\'t forget to hit \'Save Changes!\' to apply!'),
            array($this, 'options_callback'),
            ryansPaymentButton::$page_name
        );

        add_settings_field(
            'paypal_id', 
            __('PayPal id/Email:'), 
            array($this,'paypal_id_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'paypal_id' )
        );

         add_settings_field(
            'currency', 
            __('Currency:'), 
            array($this,'currency_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'currency' )
        );

        add_settings_field(
            'type', 
            __('Type:'), 
            array($this,'type_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'type' )
        );


        add_settings_field(
            'size', 
            __('Size:'), 
            array($this,'size_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'size' )
        );


        add_settings_field(
            'button_id', 
            __('Choose a button:'), 
            array($this,'button_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'button_id' )
        );

        add_settings_field(
            'textbox_location', 
            __('Textbox location:'), 
            array($this,'textbox_location_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'textbox_location' )
        );

        add_settings_field(
            'amount_description', 
            __('Text:'), 
            array($this,'amount_description_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'amount_description' )
        );

       add_settings_field(
            'amount_default', 
            __('Amount Default:'), 
            array($this,'amount_default_callback'), 
            ryansPaymentButton::$page_name, 
            $section_name,
            array( 'label_for' => 'amount_default' )
        );
    }


    function amount_default_callback(){
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;

        $amount_default = 20.00;
        if(isset($options['amount_default'])){
            $amount_default = $options['amount_default'];
        }

        echo "<input class='regular-text ltr' id='amount_default' name='{$current_options_name}[amount_default]' onkeyup='ryans_payment_button_check_decimal(this)' value='{$amount_default}' >";
    }


    function paypal_id_callback() {
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;

	    echo "<input class='regular-text ltr' name='{$current_options_name}[paypal_id]' id='paypal_id'  value='{$options['paypal_id']}'/>";
    }


    function amount_description_callback(){
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;

        $amount_description = $this->default_amount_description;
        if(isset($options['amount_description'])){
            $amount_description = $options['amount_description'];
        }
 
        echo "<input class='regular-text ltr' name='{$current_options_name}[amount_description]' id='amount_description'  value='{$amount_description}'/>";        
    }


    function textbox_location_callback(){
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;

        $textbox_location = 'top';
        if(isset($options['textbox_location'])){
            $textbox_location = $options['textbox_location'];
        }

        ?>
        <select id='ryans_payment_button_textbox_location' name='<?= $current_options_name ?>[textbox_location]'>
            <option value='hidden' <?php if($textbox_location  == 'hidden') { echo 'selected'; }  ?>>Hidden</option>
            <option value='top' <?php if($textbox_location  == 'top') { echo 'selected'; }  ?>>Top</option>
        </select>
        <?php                  
    }


    function size_callback() {
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;

        $button_size = 'medium';
        if(isset($options['size'])){
            $button_size = $options['size'];    
        }

        ?>
        <select id='ryans_payment_button_size' name='<?= $current_options_name ?>[size]'>
            <option value='small' <?php if($button_size == 'small') { echo 'selected'; }  ?>>Small</option>
            <option value='medium' <?php if($button_size == 'medium') { echo 'selected'; }  ?>>Medium</option>
            <option value='large' <?php if($button_size == 'large') { echo 'selected'; }  ?>>Large</option>
            <option value='extralarge' <?php if($button_size == 'extralarge') { echo 'selected'; }  ?>>Extra Large</option>
        </select>
        <?php                  
    }


    function type_callback(){
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;        

        $button_type = 'buynow';
        if(isset($options['button_type'])){
            $button_type = $options['button_type'];    
        }

        ?>
        <select id='ryans_payment_button_type' name='<?= $current_options_name ?>[button_type]'>
            <option value='buynow' <?php if($button_type == 'buynow') { echo 'selected'; }  ?>>Buy Now</option>
            <option value='donate' <?php if($button_type == 'donate') { echo 'selected'; }  ?>>Donate</option>
        </select>
        <?php                  
    }


    function button_callback(){
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;

        foreach(ryansPaymentButton::$paypal_buttons as $id => $button_info) :
            $size = $button_info[0];
            $url = $button_info[1];

            $type = 'buynow';
            if(isset($button_info[2]))
            {
                $type = $button_info[2];          
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
                <label class="ryans-payment-button-label" data-button-size='<?= $size ?>' data-button-type='<?= $type ?>' >
                <input type='radio' name='<?= $current_options_name ?>[button_id]' value='<?= $id ?>' <?= $is_checked ?>>
                <img src='<?= $url ?>' style='vertical-align: middle; margin: 10px;'>
                </label>
            </p>
            <?php          
        endforeach;	
    }


    function currency_callback() {
        $options = get_option(ryansPaymentButton::$options_name);
        $current_options_name = ryansPaymentButton::$options_name;    

        $currencies = array(
            'AUD' => 'Australian Dollars (A $)',
            'BRL' => 'Brazilian Real',
            'CAD' => 'Canadian Dollars (C $)',
            'CZK' => 'Czech Koruna',
            'DKK' => 'Danish Krone',
            'EUR' => 'Euros (€)',
            'HKD' => 'Hong Kong Dollar ($)',
            'HUF' => 'Hungarian Forint',
            'ILS' => 'Israeli New Shekel',
            'JPY' => 'Yen (¥)',
            'MYR' => 'Malaysian Ringgit',
            'MXN' => 'Mexican Peso',
            'NOK' => 'Norwegian Krone',
            'NZD' => 'New Zealand Dollar ($)',
            'PHP' => 'Philippine Peso',
            'PLN' => 'Polish Zloty',
            'GBP' => 'Pounds Sterling (£)',
            'RUB' => 'Russian Ruble',
            'SGD' => 'Singapore Dollar ($)',
            'SEK' => 'Swedish Krona',
            'CHF' => 'Swiss Franc',
            'TWD' => 'Taiwan New Dollar',
            'THB' => 'Thai Baht',
            'TRY' => 'Turkish Lira',
            'USD' => 'U.S. Dollars ($)',
        );

        $currency = 'USD';
        if(isset($options['currency'])){
            $currency  = $options['currency'];
        }

        ?>
        <select id='currency' name='<?= $current_options_name ?>[currency]'>
        <?php
            foreach($currencies as $code => $description) :
                if( $code == $currency ){ 
                    $selected = "selected"; 
                } 
                else { 
                    $selected = ""; 
                }
                echo "<option {$selected} value='{$code}'>{$description}</option>";
            endforeach;	
        ?>
        </select>
        <?php
    }


    function options_callback() {
    }


    public function sanitize( $input ){
        return $input;
    }
}
    