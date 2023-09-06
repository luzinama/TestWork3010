<?php

use TestWork\CreateProductForm;
use TestWork\WoocommerceCustomFields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class TestWork {
    static $theme_uri;
    static $theme_path;

    public static function load() {
        self::$theme_uri      = trailingslashit( get_stylesheet_directory_uri() );
        self::$theme_path     = trailingslashit( get_stylesheet_directory() );


        self::autoload();
        self::load_scripts();
        self::load_modules();
    }

    public static function autoload() {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            $file = str_replace('TestWork', 'inc', $file);
            $file = lcfirst($file);
            $file =  plugin_dir_path( __FILE__ ).  $file;

            if (file_exists($file)) {
                require $file;
                return true;
            }

            return false;
        });
    }

    public static function load_scripts() {
        add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_admin_scripts' );
        add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_scripts' );

        wp_localize_script( 'jquery', 'TestWorkVars',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            )
        );
    }

    public static function enqueue_admin_scripts($hook) {
        if ( 'post.php' == $hook ) {
            if (get_post_type() === 'product') {
                wp_enqueue_script( 'test-work-admin-scripts',
                    self::$theme_uri . '/assets/js/admin-scripts.js',
                    array( 'jquery' ),
                    false,
                    true
                );
                self::localize_script('test-work-admin-scripts');
            }
        }
    }

    public static function enqueue_scripts() {
        wp_enqueue_script( 'test-work-scripts',
            self::$theme_uri . '/assets/js/scripts.js',
            array( 'jquery' ),
            false,
            true
        );

        self::localize_script('test-work-scripts');
    }

    public static function localize_script($handle) {
        wp_localize_script( $handle, 'TestWorkVars',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            )
        );
    }

    public static function load_modules() {
        WoocommerceCustomFields::init();
        CreateProductForm::init();
    }
}

TestWork::load();
