<?php

use Google\Client;


class CCPT_Google_Auth {
    public static $client = null;
    private static $client_id = null;
    private static $client_secret = null;
    private static $redirect_url = null;

    public static function init(){
        self::$client_id = get_theme_mod( 'google_client_id' );
        self::$client_secret = get_theme_mod( 'google_client_secret' );
        self::$redirect_url = home_url();
        
        self::config_client();
    }

    private static function config_client(){
        if( self::$client_id && self::$client_secret ){
            self::$client = new Google_Client();

            self::$client->setClientId( self::$client_id );
            self::$client->setClientSecret( self::$client_secret );
            self::$client->addScope( [Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE] );
            self::$client->setAccessType( 'offline' );
            self::$client->setPrompt( 'select_account' );
            self::$client->setRedirectUri( self::$redirect_url );

            self::authenticate();
        }
    }

    public static function get_auth_url(){
        return self::$client->createAuthUrl();
    }


    public static function authenticate(){
        if( isset( $_GET['code'] ) ){
            $access_token = false;

            if( isset( $_COOKIE['g_access_token'] ) ){
                $cookie_token = json_decode( $_COOKIE['g_access_token'], true );

                self::$client->setAccessToken( $cookie_token );
                
                if( self::$client->isAccessTokenExpired() ){
                    self::$client->fetchAccessTokenWithRefreshToken( self::$client->getRefreshToken() );
                }

                $access_token = self::$client->getAccessToken();
            }
            else{
                $access_token = self::$client->fetchAccessTokenWithAuthCode( $_GET['code'] );
            }

            if( !isset( $access_token['error'] ) ){
                setcookie( 'g_access_token', json_encode( $access_token ), time() + 604800, '', '', false, true );

                $oauth = new Google_Service_Oauth2( self::$client );
        
                $profile = $oauth->userinfo->get();
        
                if( !email_exists( $profile->email ) ){
                    $registered_user_id = ccpt_create_new_user( $profile->email, null, $profile->email, array(
                        'first_name'        => $profile->first_name,
                        'last_name'         => $profile->last_name,
                        'google_id'         => $profile->id,
                        'avatar_url'        => $profile->picture
                    ) );
        
                    wp_set_current_user( $registered_user_id );
                    wp_set_auth_cookie( $registered_user_id );
                }
                else{
                    $registered_user = get_user_by( 'email', $profile->email );

                    ccpt_link_social_id( $registered_user->ID, 'facebook', $profile->id );
                    ccpt_set_avatar_from_url( $registered_user->ID, $profile->picture );

                    wp_set_current_user( $registered_user->ID );
                    wp_set_auth_cookie( $registered_user->ID );
                }
        
                wp_redirect( get_permalink( get_theme_mod( 'profile_page' ) ) );
                exit();
            }
        }
    }
}


add_action( 'plugins_loaded', function(){
    CCPT_Google_Auth::init();
} );