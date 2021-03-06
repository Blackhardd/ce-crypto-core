<?php

/**
 * CeCrypro Core AJAX handlers.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


class CCPT_AJAX {
    public static function init(){
        self::add_events();
    }


    public static function add_events(){
        $events = array(
            'register',
            'login',
            'lost_password',
            'search',
            'search_articles',
            'save_profile',
            'facebook_auth',
            'contact',
            'load_terms',
            'like_article'
        );

        foreach( $events as $event ){
            add_action( "wp_ajax_{$event}", array( __CLASS__, $event ) );
            add_action( "wp_ajax_nopriv_{$event}", array( __CLASS__, $event ) );
        }
    }
    

    public static function register(){
        $user_id = ccpt_create_new_user( $_POST['email'], null, $_POST['password'], array(
            'first_name'    => $_POST['first_name'],
            'last_name'     => $_POST['last_name'],
            'twitter'       => $_POST['twitter'],
            'telegram'      => $_POST['telegram']
        ) );

        if( is_wp_error( $user_id ) ){
            ccpt_send_ajax_response( 'error', $user_id->get_error_message() );
        }
        
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        ccpt_send_ajax_response( 'redirect', home_url() );
    }


    public static function login(){
        $user = wp_signon( array(
            'user_login'    => $_POST['email'],
            'user_password' => $_POST['password']
        ) );

        if( is_wp_error( $user ) ){
            ccpt_send_ajax_response( 'error', $user->get_error_message() );
        }

        ccpt_send_ajax_response( 'redirect', home_url() );
    }


    public static function lost_password(){
        $is_password_updated = ccpt_reset_user_password( $_POST['email'] );

        if( is_wp_error( $is_password_updated ) ){
            ccpt_send_ajax_response( 'error', $is_password_updated->get_error_message() );
        }

        ccpt_send_ajax_response( 'success', __( '?????????? ???????????? ???????? ?????????????????? ?????? ???? ??????????. ?????????? ??????????????!', 'ce-cerypto' ) );
    }


    public static function search(){
        $results = ccpt_search_posts_by_title( $_POST['keyword'], $_POST['post_type'] );

        $html = '';

        if( !empty( $results ) ){
            switch( $_POST['post_type'] ){
                case 'term':
                    foreach( $results as $post ){
                        $html .= "<a>{$post['title']}</a>";
                    }
                    break;
                default:
                    foreach( $results as $post ){
                        $html .= "<a href='{$post['link']}'>{$post['title']}</a>";
                    }
                    break;
            }
            
            ccpt_send_ajax_response( 'html', $html, array(
                'have_results'  => true
            ) );
        }
        else{
            $html .= "<div class='nothing-found nothing-found--header-search'>";
            $html .= __( '???? ?????????????? ???????????? ???? ????????????????.', 'ce-crypto' );
            $html .= "</div>";

            ccpt_send_ajax_response( 'html', $html, array(
                'have_results'  => false
            ) );
        }
    }


    public static function search_articles(){
        $results = ccpt_search_articles_by_title( $_POST['keyword'] );

        $html = '';

        if( !empty( $results ) ){
            foreach( $results as $article ){
                $html .= "<a href='{$article['link']}'>{$article['title']}</a>";
            }
        }
        else{
            $html .= "<div class='nothing-found nothing-found--header-search'>";
            $html .= __( '???? ?????????????? ???????????? ???? ????????????????.', 'ce-crypto' );
            $html .= "</div>";
        }

        ccpt_send_ajax_response( 'html', $html );
    }


    public static function save_profile(){
        $avatar_attachment_id = false;

        if( isset( $_FILES ) ){
            $avatar_attachment_id = media_handle_upload( 'avatar', 0 );
            update_post_meta( $avatar_attachment_id, '_is_avatar', true );
        }

        $user_id = ccpt_update_profile( wp_get_current_user()->ID, array(
            'full_name'     => $_POST['full_name'],
            'email'         => $_POST['email'],
            'phone'         => $_POST['phone'],
            'password'      => $_POST['password'],
            'twitter'       => $_POST['twitter'],
            'telegram'      => $_POST['telegram'],
            'avatar'        => $avatar_attachment_id
        ) );

        if( is_wp_error( $user_id ) ){
            ccpt_send_ajax_response( 'error', $user_id->get_error_message() );
        }

        ccpt_send_ajax_response( 'success', __( '?????????????? ?????????????? ????????????????.', 'ce-crypto' ) );
    }

    
    public static function facebook_auth(){
        if( !email_exists( $_POST['email'] ) ){
            $user_id = ccpt_create_new_user( $_POST['email'], null, $_POST['email'], array(
                'first_name'    => $_POST['first_name'],
                'last_name'     => $_POST['last_name'],
                'facebook_id'   => $_POST['user_id'],
                'avatar_url'    => $_POST['avatar']
            ) );

            if( is_wp_error( $user_id ) ){
                ccpt_send_ajax_response( 'error', $user_id->get_error_message() );
            }

            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );
        }
        else{
            $user = get_user_by( 'email', $_POST['email'] );

            if( !ccpt_is_user_linked_facebook( $user->ID ) ){
                ccpt_link_social_id( $user->ID, 'facebook', $_POST['user_id'] );
                ccpt_set_avatar_from_url( $user->ID, $_POST['avatar'] );
            }

            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID );
        }

        ccpt_send_ajax_response( 'redirect', get_permalink( get_theme_mod( 'profile_page' ) ) );
    }


    public static function contact(){
        $theme = __( '???????????? ?? ???????????????????? ??????????', 'ce-crypto' );
        $message = "
            <h1>???????????? ?? ???????????????????? ??????????</h1>
            <p><b>????'??:</b> {$_POST['name']}<br/><b>Email:</b> <a href='mailto:{$_POST['email']}'>{$_POST['email']}</p>
        ";

        if( ccpt_send_admins_notification( $theme, $message ) ){
            ccpt_send_ajax_response( 'success', __( '?????????????????? ?????????? ??????????????.', 'ce-crypto' ) );
        }
        
        ccpt_send_ajax_response( 'error', __( '???????? ?????????? ???? ??????. ?????????????????? ???? ??????.', 'ce-crypto' ) );
    }


    public static function load_terms(){
        $current_char = $_POST['char'];

        $args = json_decode( stripslashes( $_POST['posts'] ), true );
        $args['paged'] = $_POST['page'] + 1;
        $args['post_status'] = 'publish';

        query_posts( $args );

        ob_start();

        if( have_posts() ) :
            while( have_posts() ) :
                the_post();

                if( $current_char !== mb_substr( get_the_title(), 0, 1 ) ){
                    $current_char = mb_substr( get_the_title(), 0, 1 );
                    ccpt_get_terms_separator_template( $current_char );
                }
                
                ccpt_get_term_template();
            endwhile;

            $html = ob_get_clean();

            ccpt_send_ajax_response( 'html', $html, array(
                'current_char'  => $current_char
            ) );
        else :
            ccpt_send_ajax_response( 'failure', __( '???????? ???????????????? ?????? ????????????.', 'ce-crypto' ), array(
                'current_char'  => $current_char
            ) );
        endif;
    }


    public static function like_article(){
        if( !empty( $_POST['post_id'] ) ){
            $is_updated = ccpt_update_article_likes( $_POST['post_id'] );

            if( $is_updated ){
                ccpt_send_ajax_response( 'success' );
            }
        }

        ccpt_send_ajax_response( 'failure' );
    }
}


CCPT_AJAX::init();