<?php

/**
 * Plugin core file.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


class CCPT_Core {
    /**
     * Plugin version.
     * 
     * @var string
     */
    public $version = CCPT_CORE_VERSION;


    /**
     * CeCrypto core instance.
     * 
     * @var CCPT_Core
     */
    protected static $_instance = null;


    /**
     * Singleton.
     * 
     * @since 0.0.1
     * @static
     * @return CCPT_Core - instance
     */
    public static function instance(){
        if( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }


    /**
     * Cloning is forbidden.
     * 
     * @since 0.0.1
     */
    public function __clone(){
        _doing_it_wrong( __FUNCTION__, __( 'Клонування заборонено.', 'ce-crypto' ) . wp_debug_backtrace_summary(), '0.0.1' );
    }


    /**
     * Unserializing is forbidden.
     * 
     * @since 0.0.1
     */
    public function __wakeup(){
        _doing_it_wrong( __FUNCTION__, __( 'Десеріалізація екземпляру класа заборонена.', 'ce-crypto' ) . wp_debug_backtrace_summary(), '0.0.1' );
    }


    /**
     * Constructor.
     *
     */
    public function __construct(){
        $this->includes();
    }


    /**
     * Including required files.
     */
    public function includes(){
        include_once CCPT_CORE_PATH . '/inc/ccpt-core-functions.php';
        include_once CCPT_CORE_PATH . '/inc/classes/class-ccpt-post-types.php';
        include_once CCPT_CORE_PATH . '/inc/classes/class-ccpt-shortcodes.php';
        include_once CCPT_CORE_PATH . '/inc/classes/customizer/class-ccpt-customizer.php';
        include_once CCPT_CORE_PATH . '/inc/classes/class-ccpt-ajax.php';
        include_once CCPT_CORE_PATH . '/inc/classes/auth/class-ccpt-google-auth.php';
        include_once CCPT_CORE_PATH . '/inc/classes/class-ccpt-frontend.php';
    }
}