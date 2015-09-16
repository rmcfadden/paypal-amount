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

    public static function init() {
        $class = __CLASS__;
        new $class;

    }

    public static function activation() {
        $new_options = array(
            'paypal_id' => get_option('admin_email') 
        );

	    if ( get_option(paypalAmount::$options_name ) !== false ) {
      	    update_option(paypalAmount::$options_name, $new_options );
        } else{
   		    add_option(paypalAmount::$options_name, $new_options );
        }

    }


    public function __construct() {

        error_log('IN CONSTRUCT!!!');
 
        //add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
        //add_filter( 'the_content', array( $this, 'append_post_notification' ) ); 
        add_shortcode( 'paypal_amount', array( $this, 'shortcode' )); 
        add_action('admin_menu', array( $this, 'admin_option_init'));
        add_action('admin_init', array( $this, 'admin_init' ));
    }



    public function shortcode() {

        $image_url = 'https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif';

	    return '<form  target="' . $options['target'] . '" action="https://www.paypal.com/cgi-bin/webscr" method="post">
    			<div class="paypal_donation_button">
			        <input type="hidden" name="business" value="' . $options['paypay_id'] . '">
			        <input type="hidden" name="currency_code" value="' . $options['currency_code'] .'">
			        <input type="image" src="'. $image_url . '" name="submit">
			        <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
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
                // This prints out all hidden setting fields
                settings_fields(paypalAmount::$options_name );   
                do_settings_sections( paypalAmount::$page_name );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }


    public function admin_init() {

        register_setting(
            paypalAmount::$options_name,
            paypalAmount::$options_name,
            array( $this, 'sanitize' )
        );

        $section_name = paypalAmount::$options_name + '_section';

        add_settings_section(
            $section_name,
            'Alter your paypal amount settings below:',
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
    }


    function paypal_id_callback() {
        $options = get_option(paypalAmount::$options_name);
        $current_options_name = paypalAmount::$options_name;


	    echo "<input class='regular-text ltr' name='{$current_options_name}[paypal_id]' id='paypal_id' type='email' value='{$options['paypal_id']}'/>";
    }

    function options_callback() {
    }


/*
    function paypal_donation_button_target_callback() {
	    $options = get_option('paypal_donation_button_options');
	    $target = array(
		    '_blank' => 'Blank',
		    '_self' => 'Self'
	    );
	    ?>
	    <select id='target' name='paypal_donation_button_options[target]'>
		    <?php
			    foreach($target as $key => $label) :
				    if( $key == $options['target'] ) { $selected = "selected='selected'"; } else { $selected = ''; }
				    echo "<option {$selected} value='{$key}'>{$label}</option>";
			    endforeach;
		    ?>
	    </select>
	    <p class="description"><?php _e('Select "Blank" to open the PayPal window in a new window or tab (this is default). Selcet "Self" to open the PayPal window in the same frame as it was clicked.') ?></p>
	    <?php
    }
*/


    public function sanitize( $input ){

/*
        $new_input = array();
        if( isset( $input['pa'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );
*/
        return $input;
    }
}
    