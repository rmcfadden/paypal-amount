jQuery(document).ready(function () {

    ryans_payment_button_admin_filter_by_size();

    jQuery('#ryans_payment_button_size').change(ryans_payment_button_admin_filter_by_size);
    jQuery('#ryans_payment_button_type').change(ryans_payment_button_admin_filter_by_size);

});


function ryans_payment_button_admin_filter_by_size(){

    var size = jQuery('#ryans_payment_button_size').val();
    var type = jQuery('#ryans_payment_button_type').val();
    

    jQuery('.ryans-payment-button-label').hide();   
    
    jQuery('[data-button-size="' + size + '"][data-button-type="' + type + '"]').show();
}