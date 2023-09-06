<?php

namespace TestWork;

if (!defined('ABSPATH')) {
    exit;
}

class WoocommerceCustomFields
{
    public static function init()
    {
        add_filter('woocommerce_product_data_tabs', __CLASS__. '::product_custom_fields_tab');
        add_action('woocommerce_product_data_panels', __CLASS__. '::product_custom_fields_panel');
        add_action('woocommerce_process_product_meta', __CLASS__. '::product_custom_fields_save');

        add_filter('woocommerce_product_get_image', __CLASS__. '::product_custom_image');
        add_action('woocommerce_after_shop_loop_item_title', __CLASS__. '::product_custom_fields');
    }


    public static function product_custom_fields_tab($tabs)
    {
        $tabs['custom'] = array(
            'label' => __('Custom Fields'),
            'target' => 'product_custom_fields_panel',
            'priority' => 5,
        );
        return $tabs;
    }

    public static function product_custom_fields_panel()
    {
        echo '<div id="product_custom_fields_panel" class="panel woocommerce_options_panel">';

        woocommerce_wp_text_input( array(
            'id'            => '_custom_product_image_button',
            'type'          => 'button',
            'label'         => __( 'Product Image' ),
            'value'         => 'Choose an Image',
            'class'         => 'button',
        ) );

        woocommerce_wp_text_input( array(
            'id'            => '_custom_product_image',
            'type'          => 'hidden',
            'label'         => '',
            'value'         => get_post_meta( get_the_ID(), '_custom_product_image', true ),
            'data_type'     => 'url'
        ) );

        $url = get_post_meta(get_the_ID(), '_custom_product_image', true);

        echo '<p class="form-field custom-img-preview" style="'.($url == '' ? 'display: none;' : '').'"><label></label><img width="70" height="70" src="' . $url . '"/><span class="remove-file-btn"></span></p>';

        woocommerce_wp_text_input(
            [
                'id' => '_custom_product_creation_date',
                'placeholder' => __('Product Creation Date'),
                'label' => __('Product Creation Date'),
                'type' => 'date',
                'value'         => get_post_meta( get_the_ID(), '_custom_product_creation_date', true ),
            ]
        );

        woocommerce_wp_select(
            [
                'id' => '_custom_product_type',
                'placeholder' => __('Product Type'),
                'label' => __('Product Type'),
                'options' => [
                    '' => '---',
                    'rare' => __('Rare'),
                    'frequent' => __('Frequent'),
                    'unusual' => __('Unusual')
                ],
                'value'         => get_post_meta( get_the_ID(), '_custom_product_type', true ),
            ]
        );

        echo '</div>';
    }

    // Save the custom fields when the product is saved.
    public static function product_custom_fields_save($product_id)
    {
        if (!(isset($_POST['woocommerce_meta_nonce']) || wp_verify_nonce(sanitize_key($_POST['woocommerce_meta_nonce']), 'woocommerce_save_data'))) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        $fields = [
            '_custom_product_image',
            '_custom_product_creation_date',
            '_custom_product_type',
        ];
        foreach ($fields as $field) {
            if (array_key_exists($field, $_POST)) {
                update_post_meta($product_id, $field, sanitize_text_field(esc_attr($_POST[$field])));
            }
        }

        return true;
    }

    public static function product_custom_fields() {
        $product_creation_date = get_post_meta(get_the_ID(), '_custom_product_creation_date', true);
        $product_type = get_post_meta(get_the_ID(), '_custom_product_type', true);
        if ($product_creation_date) {
            ?>
            <p><b><?php echo __('Product Creation Date') ?>:</b> <?php echo $product_creation_date ?></p>
            <?php
        }
        if ($product_type) {
            ?>
            <p><b><?php echo __('Product Type') ?>:</b> <?php echo $product_type ?></p>
            <?php
        }
    }

    public static function product_custom_image($image) {
        $url = get_post_meta(get_the_ID(), '_custom_product_image', true);

        if ( ! $url ) {
            $image = wc_placeholder_img();
        } else {
            $thumbnail_id = attachment_url_to_postid($url);
            $image = wp_get_attachment_image($thumbnail_id, 'woocommerce_thumbnail');
        }

        return $image;
    }
}
