<?php

/**
 * CeCrypro Core AJAX handlers.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


class CCPT_Customizer {
    /**
     * Constructor.
     */
    public function __construct(){
        add_action( 'customize_register', array( $this, 'add_sections' ) );
    }


    /**
     * Add customizer settings.
     * 
     * @param WP_Customize_Manager $wp_customize
     */
    public function add_sections( $wp_customize ){
        $this->add_socials_login_section( $wp_customize );
    }


    /**
     * Socials login section.
     * 
     * @param WP_Customize_Manager $wp_customize
     */
    private function add_socials_login_section( $wp_customize ){
        $wp_customize->add_panel( 'socials_login_panel', array(
            'title'         => __( 'Вхід через соціальні мережі', 'ce-crypto' ),
            'priority'      => 160,
            'capability'    => 'customize'
        ) );


        $wp_customize->add_section( 'socials_login_facebook_section', array(
            'title'         => __( 'Facebook', 'ce-crypto' ),
            'panel'         => 'socials_login_panel'
        ) );

        $wp_customize->add_setting( 'facebook_app_id', array(
            'default'       => '',
        ) );

        $wp_customize->add_control( 'facebook_app_id', array(
            'id'            => 'facebook_app_id_control',
            'label'         => __( 'App ID', 'ce-crypto' ),
            'section'       => 'socials_login_facebook_section'
        ) );


        $wp_customize->add_section( 'socials_login_google_section', array(
            'title'         => __( 'Google', 'ce-crypto' ),
            'panel'         => 'socials_login_panel'
        ) );

        $wp_customize->add_setting( 'google_client_id', array(
            'default'       => '',
        ) );

        $wp_customize->add_control( 'google_client_id', array(
            'id'            => 'google_client_id_control',
            'label'         => __( 'Client ID', 'ce-crypto' ),
            'section'       => 'socials_login_google_section'
        ) );

        $wp_customize->add_setting( 'google_client_secret', array(
            'default'       => '',
        ) );

        $wp_customize->add_control( 'google_client_secret', array(
            'id'            => 'google_client_secret_control',
            'label'         => __( 'Client secret', 'ce-crypto' ),
            'section'       => 'socials_login_google_section'
        ) );
    }
}


new CCPT_Customizer();