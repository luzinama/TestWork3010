var Woo_Custom_Fields = {

    uploadProductImage: function () {
        var custom_uploader;
        jQuery('#_custom_product_image_button').click(function(e) {

            e.preventDefault();

            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }

            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose an Image',
                button: {
                    text: 'Choose an Image'
                },
                multiple: false
            });

            //When a file is selected, grab the URL and set it as the text field's value
            custom_uploader.on('select', function() {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                jQuery('#_custom_product_image').val(attachment.url);
                jQuery('.custom-img-preview img').attr('src', attachment.url);
                jQuery('.custom-img-preview').show();
            });

            //Open the uploader dialog
            custom_uploader.open();

        });
    },

    removeProductImage: function () {
        jQuery('#_custom_product_image').val('');
        jQuery('.custom-img-preview img').attr('src', '');
        jQuery('.custom-img-preview').hide();
    },

    addClearFieldsButton: function() {
        let reset_btn = '<button id="custom-reset-button" class="button button-primary">Reset Custom Fields</button>';
        jQuery('#product_custom_fields_panel').append(reset_btn);
        jQuery('#custom-reset-button').click(function(e) {
            e.preventDefault();
            Woo_Custom_Fields.removeProductImage();
            jQuery('#_custom_product_creation_date').val('');
            jQuery('#_custom_product_type option:first').prop('selected',true).trigger( "change" );
        });
    },

    addNewSubmit: function() {
        let submit_btn = '<button id="custom-submit-button" class="button button-primary">Submit Product</button>';
        jQuery('#product_custom_fields_panel').append(submit_btn);

        // Bind the click event to the new button.
        jQuery('#custom-submit-button').on('click', function () {
            // Get the product data.
            var productData = jQuery('#woocommerce-product-data').serialize();
            // Submit the product data.
            jQuery.ajax({
                url: TestWorkVars.ajaxurl,
                data: productData,
                type: 'POST',
                success: function (response) {
                    // Refresh the page.
                    window.location.reload();
                }
            });
        });
    },

    init: function () {
        Woo_Custom_Fields.uploadProductImage();
        Woo_Custom_Fields.addNewSubmit();
        Woo_Custom_Fields.addClearFieldsButton();
    }
};


jQuery(document).ready( function(){
    Woo_Custom_Fields.init();

});