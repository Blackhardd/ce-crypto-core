<?php

/**
 * CeCrypto Term functions.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


/**
 * Get order weight from post title.
 * 
 * @param string $post_title
 * @return integer
 */
function ccpt_get_title_order_weight( $post_title ){
    $numbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
    $ukrainian_letters = ccpt_get_ukrainian_alphabet();
    $english_letters = ccpt_get_english_alphabet();

    $both_letters = array_merge( $ukrainian_letters, $english_letters );

    return array_search( mb_strtolower( ccpt_get_string_first_char( $post_title ) ), $both_letters );
}


// Update term order weight meta.

add_action( 'save_post_term', 'ccpt_update_term_order_weight', 10, 3 );

function ccpt_update_term_order_weight( $post_id, $post, $update ){
    update_post_meta( $post_id, 'ccpt_order_weight', ccpt_get_title_order_weight( $post->post_title ) );
}


// Modify terms archive query.

add_action( 'pre_get_posts', 'ccpt_modify_terms_archive_query' );

function ccpt_modify_terms_archive_query( $query ){
    if( !is_admin() && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === 'term' ){
        $query->set( 'posts_per_page', 3 );

        if( isset( $_GET['letter'] ) ){
            $query->set( 'meta_query', [
                array(
                    'key'   => 'ccpt_order_weight',
                    'value' => $_GET['letter']
                )
            ] );
        }

        if( isset( $_GET['search'] ) ){
            $query->set( 'search_title', $_GET['search'] );
        }

        if( isset( $_GET['item'] ) ){
            $query->set( 'p', $_GET['item'] );
        }

        $query->set( 'meta_key', 'ccpt_order_weight' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'ASC' );
    }
}