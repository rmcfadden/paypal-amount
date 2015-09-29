jQuery(document).ready(function () {

    paypal_amount_admin_filter_by_button_size();

    jQuery('#paypal_amount_button_size').change(paypal_amount_admin_filter_by_button_size);
    jQuery('#paypal_amount_button_type').change(paypal_amount_admin_filter_by_button_size);

});


function paypal_amount_admin_filter_by_button_size(){

    var button_size = jQuery('#paypal_amount_button_size').val();
    var button_type = jQuery('#paypal_amount_button_type').val();
    jQuery('.paypal-amount-button-label').hide();   
    
    jQuery('[data-button-size="' + button_size + '"][data-button-type="' + button_type + '"]').show();
}
      