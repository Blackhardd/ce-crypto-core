<?php

/**
 * Handle frontend scripts and styles.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


class CCPT_Frontend {
    /**
     * Array of registered script names.
     * 
     * @var string[]
     */
    private static $scripts = array();


    /**
     * Array of localized script names.
     * 
     * @var string[]
     */
    private static $localized_scripts = array();


    /**
     * Array of style handles.
     * 
     * @var string[]
     */
    private static $styles = array();


    /**
     * Hook in methods.
     */
    public static function init(){
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
        add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
        add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
    }


    /**
     * Get public assets URL.
     * 
     * @param string $path
     * @return string
     */
    private static function get_public_asset_url( $path ){
        return plugins_url( 'assets/public/' . $path, CCPT_CORE_FILE );
    }


    /**
     * Register scripts.
     */
    private static function register_scripts(){
        $scripts_to_register = array(
            'facebook-login'    => array(
                'src'               => self::get_public_asset_url( 'js/facebook-login.js' ),
                'deps'              => ['jquery'],
                'version'           => CCPT_CORE_VERSION
            )
        );

        foreach( $scripts_to_register as $name => $props ){
            self::$scripts[] = $name;
            wp_register_script( $name, $props['src'], $props['deps'], $props['version'], true );
        }
    }


    public static function load_scripts(){
        self::register_scripts();
    }


    /**
     * Localize printed scripts.
     */
    public static function localize_printed_scripts(){
        foreach( self::$scripts as $name ){
            if( !in_array( $name, self::$localized_scripts, true ) && wp_script_is( $name ) ){
                $data = self::get_localization_data( $name );
                self::$localized_scripts[] = $name;
                wp_localize_script( $name, str_replace( '-', '_', $name ) . '_params', $data );
            }
        }
    }


    /**
     * Return localization data for script.
     * 
     * @param string $name
     * @return array|bool
     */
    private static function get_localization_data( $name ){
        switch ( $name ){
            case 'facebook-login':
                $params = array(
                    'ajax_url'          => admin_url( 'admin-ajax.php' ),
                    'app_id'            => get_theme_mod( 'facebook_app_id' )
                );
                break;
            default:
                $params = false;
        }

        return $params;
    }
}


CCPT_Frontend::init();