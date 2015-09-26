jQuery(document).ready(function () {

    paypal_amount_admin_filter_by_button_size();

    jQuery('#paypal_amount_button_size').change(paypal_amount_admin_filter_by_button_size);
});


function paypal_amount_admin_filter_by_button_size(){

    var button_size = jQuery('#paypal_amount_button_size').val();
    
    jQuery('.paypal-amount-button-label').hide();   
    jQuery('[data-button-size="' + button_size + '"]').show();
}
      