<?php

/**
 * Plugin Name: CeCrypto Core
 * Version: 0.0.1
 * 
 * @package CeCrypto Core
 */


if( !defined( 'ABSPATH' ) )
    exit;


// Defining constants.

if( !defined( 'CCPT_CORE_VERSION' ) )
    define( 'CCPT_CORE_VERSION', '0.0.1' );

if( !defined( 'CCPT_CORE_PATH' ) )
    define( 'CCPT_CORE_PATH', dirname( __FILE__ ) );

if( !defined( 'CCPT_CORE_FILE' ) )
    define( 'CCPT_CORE_FILE', __FILE__ );


// Require autoloader

require CCPT_CORE_PATH . '/vendor/autoload.php';


// Including main plugin class.

if( !class_exists( 'CCPT_Core' ) )
    include_once CCPT_CORE_PATH . '/inc/classes/class-ccpt-core.php';


/**
 * Returns main instance of plugin.
 * 
 * @since 0.0.1
 * @return CCPT_Core
 */
function CCPT(){
    return CCPT_Core::instance();
}


// Running plugin.

CCPT();


// Hook on plugin activation.

register_activation_hook( __FILE__, 'ccpt_activation' );

function ccpt_activation(){
    // Add student role.

    add_role( 'student', __( 'Студент', 'ce-crypto' ), array( 'read'  => true ) );

    // Create avatars folder.

    ccpt_maybe_create_folder_in_uploads( 'ccpt-avatars' );
}


// Hook on plugin deactivation.

register_deactivation_hook( __FILE__, 'ccpt_deactivation' );

function ccpt_deactivation(){

}