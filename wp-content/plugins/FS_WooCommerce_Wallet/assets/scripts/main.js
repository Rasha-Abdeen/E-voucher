(function (){
    
    "use strict";
    
    jQuery(document).ready(function (){
        
        jQuery('.add-credit-btn').on('click', function () {
            
            lockScroll();

            var user_id = jQuery(this).data('uid');
            
            jQuery('#add-funds input.user_id').val(user_id);
            
            jQuery('#add-funds').show();

        });
        
        jQuery('#cancel-add-fund').on('click', function () {

            unlockScroll();
            
            jQuery('#add-funds').hide();

        });
        
        
        jQuery('.withdraw-credit-btn').on('click', function () {
            
            lockScroll();
            
            var user_id = jQuery(this).data('uid');
            
            jQuery('#withdraw-funds input.user_id').val(user_id);

            jQuery('#withdraw-funds').show();

        });
        
        jQuery('#cancel-withdraw-fund').on('click', function () {
            
            unlockScroll();

            jQuery('#withdraw-funds').hide();

        });
        
        
        jQuery('.lock-credit-btn').on('click', function () {
            
            lockScroll();
            
            var user_id = jQuery(this).data('uid');
            var action  = jQuery(this).data('action');
            
            jQuery('#lock-credit input.user_id').val(user_id);
            jQuery('#lock-credit input.fsww_action').val(action);

            jQuery('#lock-credit').show();

        });
        
        jQuery('#cancel-lock-credit').on('click', function () {
            
            unlockScroll();

            jQuery('#lock-credit').hide();

        });
        
        jQuery('#select_user_id').on('change', function () {
                
            var data = {
                'action': 'fsww_user_tansactions',
                'user_id': jQuery('#select_user_id').val()
            };

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success : function(response) {
                    jQuery("#user_transaction_table_container").html(response);
                }
            });
            
        });
        
    });
    
    
    function lockScroll() {
            
            jQuery('body').css('overflow', 'hidden');
            
        }
        
    function unlockScroll() {

        jQuery('body').css('overflow', 'scroll');

    }
    
})(jQuery);