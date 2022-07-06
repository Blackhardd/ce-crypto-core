<?php

/**
 * CeCrypto Core functions.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


// Include core functions

require CCPT_CORE_PATH . '/inc/ccpt-user-functions.php';
require CCPT_CORE_PATH . '/inc/ccpt-article-functions.php';
require CCPT_CORE_PATH . '/inc/ccpt-test-functions.php';
require CCPT_CORE_PATH . '/inc/ccpt-term-functions.php';


/**
 * Get ukrainian alphabet letters array.
 * 
 * @return string[]
 */
function ccpt_get_ukrainian_alphabet(){
    $letters = ['а', 'б', 'в', 'г', 'ґ', 'д', 'е', 'є', 'ж', 'з', 'и', 'і', 'ї', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ь', 'ю', 'я'];

    return $letters;
}


/**
 * Get ukrainian alphabet letters array.
 * 
 * @return string[]
 */
function ccpt_get_english_alphabet(){
    $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];

    return $letters;
}


/**
 * Get alphabet filter items.
 * 
 * @return string[]
 */
function ccpt_get_alphabet_filter_items(){
    $ukrainian_alphabet = ccpt_get_ukrainian_alphabet();
    $english_alphabet = ccpt_get_english_alphabet();

    $both_alphabets = array_merge( $ukrainian_alphabet, $english_alphabet );

    $output = [
        array_slice( $both_alphabets, 0, count( $ukrainian_alphabet ), true ),
        array_slice( $both_alphabets, count( $ukrainian_alphabet ), count( $both_alphabets ), true )
    ];

    return $output;
}


/**
 * Get query object for specified post type.
 * 
 * @param string $post_type
 * @param int $numberposts
 * @return WP_Query
 */
function cctp_get_post_query_object( $post_type, $numberposts = 10 ){
    if( empty( $post_type ) )
        return;

    $args = array(
        'post_type'     => $post_type,
        'numberposts'   => $numberposts
    );

    return new WP_Query( $args );
}


/**
 * Get query object of an article post type.
 * 
 * @param int $numberposts
 * @return WP_Query
 */
function cctp_get_article_query_object( $numberposts = 10 ){
    return cctp_get_post_query_object( 'article', $numberposts );
}


/**
 * Get query object of a test post type.
 * 
 * @param int $numberposts
 * @return WP_Query
 */
function cctp_get_test_query_object( $numberposts = 10 ){
    return cctp_get_post_query_object( 'test', $numberposts );
}


/**
 * Get query object of a terms post type.
 * 
 * @param int $numberposts
 * @return WP_Query
 */
function cctp_get_term_query_object( $numberposts = 10 ){
    return cctp_get_post_query_object( 'terms', $numberposts );
}


/**
 * Get page choices.
 * 
 * @return string[]
 */
function ccpt_get_page_choices(){
    $pages = get_pages( array( 'hierarchical' => false ) );

    $output = array(
        '' => __( 'Оберіть сторінку', 'ce-crypto' )
    );

    foreach( $pages as $page ){
        $output[$page->ID] = $page->post_title;
    }

    return $output;
}


/**
 * Send AJAX response JSON.
 * 
 * @param string $status
 * @param string $message
 */
function ccpt_send_ajax_response( $status, $message = '', $data = array() ){
    wp_send_json( array(
        'status'    => $status,
        'message'   => $message,
        'data'      => $data
    ) );
    
    wp_die();
}


/**
 * Get AJAX post data.
 * 
 * @return string[]
 */
function ccpt_get_ajax_post_data(){
    $data = array();

    foreach( $_POST as $key => $value ){
        if( $key !== 'action' )
            $data[$key] = $value;
    }

    return $data;
}


/**
 * Maybe creates folder in wp-uploads.
 * 
 * @param string $folder_name
 * @return string
 */
function ccpt_maybe_create_folder_in_uploads( $folder_name ){
    $wp_uploads = wp_upload_dir();
    $dir = $wp_uploads['basedir'] . "/{$folder_name}";

    if( !is_dir( $dir ) ){
        wp_mkdir_p( $dir );
    }

    return $dir;
}


/**
 * Get avatar uploads dir path.
 * 
 * @return string
 */
function ccpt_get_avatar_uploads_dir(){
    return wp_upload_dir()['basedir'] . '/ccpt-avatars';
}


/**
 * Get avatar uploads dir URL.
 * 
 * @return string
 */
function ccpt_get_avatar_uploads_url(){
    return wp_upload_dir()['url'] . '/ccpt-avatars';
}


/**
 * Download image from Facebook Graph API and creating attachment.
 * 
 * @param string $url
 * @return string|int
 */
function ccpt_sideload_socials_image( $url, $user_id ){
    $image_content = file_get_contents( $url );
    $output_dir = ccpt_get_avatar_uploads_dir();
    $time = time();
    $filename = $output_dir . "/{$user_id}-{$time}.jpg";
    file_put_contents( $filename, $image_content );

    $attachment_id = wp_insert_attachment( array(
        'guid'              => ccpt_get_avatar_uploads_url() . '/' . basename( $filename ),
        'post_mime_type'    => 'image/jpg',
        'post_title'        => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
        'post_content'      => '',
        'post_status'       => 'inherit'
    ), $filename );

    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
    wp_update_attachment_metadata( $attachment_id, $attachment_data );

    return $attachment_id;
}


/**
 * Get current URL.
 * 
 * @return string
 */
function ccpt_get_current_url(){
    return home_url( str_replace( 'crypto/', '', add_query_arg( NULL, NULL ) ) );
}


/**
 * Parse and add filter query parameter.
 * 
 * @param string $filter_name
 * @param string|integer $value
 * @param string $link
 * @return string
 */
function ccpt_add_filter_query_params( $filter_name, $value, $link ){
    $output = [];

    $link = remove_query_arg( 'page', $link );

    if( isset( $_GET[$filter_name] ) ){
        $exploded = explode( ',', $_GET[$filter_name] );

        if( array_search( $value, $exploded ) === false ){
            $exploded[] = $value;
        }
        else{
            unset( $exploded[array_search( $value, $exploded )] );
        }

        if( empty( $exploded ) ){
            return remove_query_arg( $filter_name, $link );
        }
        
        return add_query_arg( array(
            $filter_name => implode( ',', $exploded )
        ), $link );
    }

    return add_query_arg( array(
        $filter_name => $value
    ), $link );
}


/**
 * Add pagination param to link.
 * 
 * @param string|integer $page_number
 * @param string $link
 * @return string
 */
function ccpt_add_pagination_query_params( $page_number, $link ){
    if( $page_number > 1 ){
        return add_query_arg( 'page', $page_number, $link );
    }
    
    return remove_query_arg( 'page', $link );
}


/**
 * Get course statuses.
 * 
 * @return string[]
 */
function ccpt_get_course_statuses(){
    return array(
        'in-progress'   => __( 'В процесі вичення', 'ce-crypto' ),
        'completed'     => __( 'Курс завершено', 'ce-crypto' )
    );
}


/**
 * Get course status title.
 * 
 * @param string $status
 * @return string
 */
function ccpt_get_course_status_title( $status ){
    $statuses = ccpt_get_course_statuses();

    if( empty( $statuses[$status] ) )
        return false;

    return $statuses[$status];
}


// Adding to WP_Query search only by title

add_filter( 'posts_where', 'ccpt_wp_query_search_by_title', 10, 2 );

function ccpt_wp_query_search_by_title( $where, $wp_query ){
    global $wpdb;

    if( $title = $wp_query->get( 'search_title' ) ){
        $where .= " AND " . $wpdb->posts . ".post_title LIKE '" . esc_sql( $wpdb->esc_like( $title ) ) . "%'";
    }

    return $where;
}


// Add plugin query vars.

add_filter( 'query_vars', 'ccpt_query_vars' );

function ccpt_query_vars( $vars ){
    $vars[] = 'second_attempt';
    $vars[] = 'unlock_test';

    return $vars;
}


if( !function_exists( 'write_log' ) ){
    function write_log( $log ){
        if( WP_DEBUG === true ){
            if( is_array( $log ) || is_object( $log ) ){
                error_log( print_r( $log, true ) );
            }
            else{
                error_log( $log );
            }
        }
    }
}