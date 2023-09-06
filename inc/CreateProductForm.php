<?php

namespace TestWork;

if (!defined('ABSPATH')) {
    exit;
}

class CreateProductForm
{
    public static function init()
    {
        add_action('wp_ajax_create_product', __CLASS__ . '::create_product');
        add_action('wp_ajax_nopriv_create_product', __CLASS__ . '::create_product');

        add_action('wp_ajax_product_image_upload', __CLASS__ . '::product_image_upload');
        add_action('wp_ajax_nopriv_product_image_upload', __CLASS__ . '::product_image_upload');
    }

    public static function create_product()
    {
        if (empty($_POST['title']) || empty($_POST['type']) || empty($_POST['date']) || empty($_POST['image']) || empty($_POST['price'])) {
            wp_send_json_error(array('message' => __('Please, fill in all fields.', 'storefront')));
        }

        $title = empty($_POST['title']) ? '' : $_POST['title'];
        $type = empty($_POST['type']) ? '' : $_POST['type'];
        $date = empty($_POST['date']) ? '' : $_POST['date'];
        $image = empty($_POST['image']) ? '' : $_POST['image'];
        $price = empty($_POST['price']) ? '' : $_POST['price'];

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field($title),
        );

        $post = wp_insert_post($args);

        if (!is_wp_error($post)) {
            $image_url = wp_get_attachment_url( $image );
            update_post_meta($post, '_custom_product_type', $type);
            update_post_meta($post, '_custom_product_image', sanitize_text_field($image_url));
            update_post_meta($post, '_custom_product_creation_date', $date);

            if ($price !== '' && $price >= 0) {
                update_post_meta($post, '_regular_price', $price);
                update_post_meta($post, '_price', $price);
            }

            $media_post = wp_update_post( array(
                'ID'            => $image,
                'post_parent'   => $post,
            ), true );

            wp_send_json_success(array(
                'success' => true,
                'message' => __('New Product was successfully created!', 'storefront')
            ));
        }
    }

    public static function product_image_upload()
    {
        $upload_dir = wp_upload_dir();

        if (isset($_FILES['product_file'])) {
            $path = $upload_dir['path'] . '/' . basename($_FILES['product_file']['name']);

            if (move_uploaded_file($_FILES['product_file']['tmp_name'], $path)) {

                $attach_id = wp_insert_attachment(array(
                    'guid' => $upload_dir['url'] . '/' . basename($_FILES['product_file']['name']),
                    'post_mime_type' => $_FILES['product_file']['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', $_FILES['product_file']['name']),
                    'post_content' => '',
                    'post_status' => 'inherit',
                ), $path);

                // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata($attach_id, $path);
                wp_update_attachment_metadata($attach_id, $attach_data);

                echo $attach_id;
//                echo $upload_dir['url'] . '/' . basename($_FILES['product_file']['name']);
            }

        }
        die;
    }
}
