<?php

/**
 * CeCrypro Core shortcodes.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


class CCPT_Shortcodes {
    public static function init(){
        self::add_shortcodes();
    }


    public static function add_shortcodes(){
        $shortcodes = array(
            'term'
        );

        foreach( $shortcodes as $shortcode ){
            add_shortcode( $shortcode, array( __CLASS__, "{$shortcode}_callback" ) );
        }
    }


    public static function term_callback( $atts, $content = '' ){
        $term_name = !empty( $atts['name'] ) ? $atts['name'] : $content;

        $term = get_page_by_title( $term_name, 'OBJECT', 'term' );

        if( !is_null( $term ) && $term->post_content ){
            $term_description = ccpt_mb_lcfirst( trim( wp_filter_nohtml_kses( apply_filters( 'the_content', $term->post_content ) ) ) );

            return "<span class='term'>{$content}<span class='term__desc'><span class='term__arrow'></span><span class='term__desc-inner'><b>{$term->post_title}</b> - {$term_description}</span></span></span>";
        }

        return $content;
    }
}


CCPT_Shortcodes::init();