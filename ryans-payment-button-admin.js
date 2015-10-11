jQuery(document).ready(function () {

    ryans_payment_button_admin_filter_by_size();

    jQuery('#ryans_payment_button_size').change(ryans_payment_button_admin_filter_by_size);
    jQuery('#ryans_payment_button_type').change(ryans_payment_button_admin_filter_by_size);

});


function ryans_payment_button_admin_filter_by_size(){

    var button_size = jQuery('#ryans_payment_button_size').val();
    var button_type = jQuery('#ryans_payment_button_type').val();
    jQuery('.paypal-amount-button-label').hide();   
    
    jQuery('[data-button-size="' + button_size + '"][data-button-type="' + button_type + '"]').show();
}