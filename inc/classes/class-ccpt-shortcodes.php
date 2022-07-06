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

        if( !is_null( $term ) ){
            $link = add_query_arg( array( 'item' => $term->ID ), get_post_type_archive_link( 'term' ) );

            return "<a href='{$link}'>{$content}</a>";
        }

        return $content;
    }
}


CCPT_Shortcodes::init();