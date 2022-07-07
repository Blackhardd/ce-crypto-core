<?php

/**
 * CeCrypto Core user functions.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


if( !function_exists( 'ccpt_create_new_user' ) ){
    /**
     * Creating a new student account.
     * 
     * @param string $email
     * @param string $username
     * @param string $password
     * @return int|WP_Error
     */
    function ccpt_create_new_user( $email, $username, $password, $args = array() ){
        if( empty( $email ) || !is_email( $email ) )
            return new WP_Error( 'registration-error-invalid-email', __( 'Будь ласка, надайте дійсну адресу електронної пошти.', 'ce-crypto' ) );

        if( email_exists( $email ) )
            return new WP_Error( 'registration-error-email-exists', __( 'На вашу адресу електронної пошти вже зареєстровано аккаунт.', 'ce-crypto' ) );

        if( empty( $username ) )
            $username = ccpt_generate_username( $email, $args );

        $username = sanitize_user( $username );

        if( empty( $username ) || !validate_username( $username ) )
            return new WP_Error( 'registration-error-invalid-username', __( 'Будь ласка, введіть валідне ім`я користувача.', 'ce-crypto' ) );

        if( username_exists( $username ) )
            return new WP_Error( 'registration-error-username-exists', __( 'Аккаунт з таким ім`ям користувача вже зареєстрований.', 'ce-crypto' ) );

        if( empty( $password ) )
            return new WP_Error( 'registration-error-missing-password', __( 'Введіть пароль для аккаунту.', 'ce-crypto' ) );

        $student_data = array(
            'user_login'    => $username,
            'user_pass'     => $password,
            'user_email'    => $email,
            'role'          => 'student'
        );

        $user_id = wp_insert_user( array_merge( $args, $student_data ) );

        if( !empty( $args['facebook_id'] ) )
            update_user_meta( $user_id, 'ccpt_facebook_id', $args['facebook_id'], true );

        if( !empty( $args['google_id'] ) )
            update_user_meta( $user_id, 'ccpt_google_id', $args['google_id'], true );

        if( !empty( $args['twitter'] ) )
            update_user_meta( $user_id, 'ccpt_twitter_username', $args['twitter'], true );

        if( !empty( $args['telegram'] ) )
            update_user_meta( $user_id, 'ccpt_telegram_username', $args['telegram'], true );

        if( !empty( $args['avatar_url'] ) )
            ccpt_set_avatar_from_url( $user_id, $args['avatar_url'] );

        return $user_id;
    }
}


/**
 * Update user profile.
 * 
 * @param string|integer $user_id
 * @param string[] $fields
 * @return integer|WP_Error
 */
function ccpt_update_profile( $user_id, $fields = array() ){
    $profile = ccpt_get_user_profile( $user_id );

    $fields_to_update = array(
        'ID'    => $user_id
    );

    if( !empty( $fields['full_name'] ) && $fields['full_name'] !== $profile['full_name'] ){
        $full_name = explode( ' ', $fields['full_name'] );

        $fields_to_update['first_name'] = $full_name[0];
        $fields_to_update['last_name'] = $full_name[1];
    }

    if( !empty( $fields['email'] ) && $fields['email'] !== $profile['email'] ){
        if( !is_email( $fields['email'] ) )
            return new WP_Error( 'save-profile-error-invalid-email', __( 'Будь ласка, надайте дійсну адресу електронної пошти.', 'ce-crypto' ) );
        
        if( email_exists( $fields['email'] ) )
            return new WP_Error( 'save-profile-error-email-exists', __( 'На таку адресу електронної пошти вже зареєстровано аккаунт.', 'ce-crypto' ) );

        $fields_to_update['email'] = $fields['email'];
    }

    if( !empty( $fields['password'] ) ){
        $fields_to_update['user_pass'] = $fields['password'];
    }

    if( !empty( $fields['phone'] ) && $fields['phone'] !== $profile['phone'] ){
        update_user_meta( $user_id, 'ccpt_phone', $fields['phone'] );
    }

    if( !empty( $fields['twitter'] ) && $fields['twitter'] !== $profile['twitter'] )
        update_user_meta( $user_id, 'ccpt_twitter_username', $fields['twitter'] );

    if( !empty( $fields['telegram'] ) && $fields['telegram'] !== $profile['telegram'] )
        update_user_meta( $user_id, 'ccpt_telegram_username', $fields['telegram'] );

    return wp_update_user( $fields_to_update );
}


/**
 * Reset user password password.
 * 
 * @param string|integer $user_email
 * @return boolean
 */
function ccpt_reset_user_password( $user_email ){
    if( !is_email( $user_email ) )
        return new WP_Error( 'save-profile-error-invalid-email', __( 'Будь ласка, надайте дійсну адресу електронної пошти.', 'ce-crypto' ) );
        
    if( !email_exists( $user_email ) )
        return new WP_Error( 'reset-password-error-email-not-exists', __( 'На таку адресу електронної пошти не зареєстровано аккаунт.', 'ce-crypto' ) );

    $user = get_user_by( 'email', $user_email );

    $password = wp_generate_password();

    $is_updated = boolval( wp_update_user( array(
        'ID'        => $user->ID,
        'user_pass' => $password
    ) ) );

    if( !$is_updated )
        return false;
    
    return wp_mail( $user_email, sprintf( __( '%s %s Зміна паролю', 'ce-crypto' ), get_bloginfo( 'name' ), '|' ),
        "
            <h1>Добрий день!</h1>
            <p>Ваш пароль був тільки що змінений на - <b>{$password}</b></p>
            <p>Слава Україні!</p>
        ",
        [
            'Content-Type: text/html; charset=UTF-8'
        ]
    );
}


/**
 * Update social network ID.
 * 
 * @param string $social
 * @param string $id
 * @return boolean
 */
function ccpt_link_social_id( $user, $social, $id ){
    return update_user_meta( $user, "ccpt_{$social}_id", $id, true );
}


/**
 * Upload and set avatar to user from URL.
 * 
 * @param string|int $user_id
 * @param string $url
 * @return string|int
 */
function ccpt_set_avatar_from_url( $user_id, $url ){
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $avatar_id = false;

    ccpt_maybe_create_folder_in_uploads( 'ccpt-avatars' );

    if( !preg_match( '/\?asid|googleusercontent/', $url ) ){
        $avatar_id = media_sideload_image( $url, 0, null, 'id' );
    }
    else{
        if( preg_match( '/\=s96/', $url ) ){
            $url = str_replace( '=s96', '=s200', $url );
        }

        $avatar_id = ccpt_sideload_socials_image( $url, $user_id );
    }

    if( $avatar_id && !is_wp_error( $avatar_id ) ){
        update_user_meta( $user_id, 'ccpt_avatar', $avatar_id, true );
    }
    
    return $avatar_id;
}


/**
 * Creating unique username.
 * 
 * @param string $email
 * @param mixed[] $args
 * @param string $suffix
 * @return string
 */
function ccpt_generate_username( $email, $args = array(), $suffix = '' ){
    $username_parts = array();

    if( isset( $args['first_name'] ) )
        $username_parts[] = sanitize_user( $args['first_name'], true );

    if( isset( $args['last_name'] ) )
        $username_parts[] = sanitize_user( $args['last_name'], true );

    $username_parts = array_filter( $username_parts );

    if( empty( $username_parts ) ){
        $email_parts = explode( '@', $email );
        $email_username = $email_parts[0];

        if( in_array( $email_username, ['sales', 'contact', 'mail'], true ) ){
            $email_username = $email_parts[1];
        }

        $username_parts[] = sanitize_user( $email_username, true );
    }

    $username = strtolower( implode( '.', $username_parts ) );

    if( $suffix ){
        $username .= $suffix;
    }

    if( username_exists( $username ) ){
        $suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
        return ccpt_generate_username( $email, $args, $suffix );
    }

    return $username;
}


/**
 * Get user by Facebook user ID.
 * 
 * @param string $facebook_id
 * @return WP_User
 */
function ccpt_get_user_by_facebook_id( $id ){
    $users = get_users( array(
        'meta_key'      => 'ccpt_facebook_id',
        'meta_value'    => $id
    ) );

    if( !isset( $user[0] ) )
        return new WP_Error( 'no-user-with-facebook-id', __( 'Користувача за таким Facebook ID не знайдено.', 'ce-crypto' ) );

    return $user[0];
}


/**
 * Get user by Google user ID.
 * 
 * @param string $google_id
 * @return WP_User
 */
function ccpt_get_user_by_google_id( $id ){
    $users = get_users( array(
        'meta_key'      => 'ccpt_google_id',
        'meta_value'    => $id
    ) );

    if( !isset( $user[0] ) )
        return new WP_Error( 'no-user-with-google-id', __( 'Користувача за таким Google ID не знайдено.', 'ce-crypto' ) );

    return $user[0];
}


/**
 * Get user profile data.
 * 
 * @param string|integer $user_id
 * @return string[]
 */
function ccpt_get_user_profile( $user_id ){
    $user = get_user_by( 'ID', $user_id );
    
    $data = array(
        'id'        => $user->ID,
        'email'     => $user->user_email,
        'full_name' => "{$user->first_name} {$user->last_name}",
        'username'  => $user->user_login,
        'avatar'    => $user->get( 'ccpt_avatar' ),
        'phone'     => $user->get( 'ccpt_phone' ),
        'twitter'   => $user->get( 'ccpt_twitter_username' ),
        'telegram'  => $user->get( 'ccpt_telegram_username' ),
        'courses'   => $user->get( 'ccpt_started_courses' )
    );

    return $data;
}


/**
 * Get current user profile data.
 * 
 * @return string[]
 */
function ccpt_get_current_user_profile(){
    $user_id = get_current_user_id();

    return ccpt_get_user_profile( $user_id );
}


/**
 * Get current user full name.
 * 
 * @return string
 */
function ccpt_get_current_user_fullname(){
    return ccpt_get_current_user_profile()['full_name'];
}


/**
 * Get current user username.
 * 
 * @return string
 */
function ccpt_get_current_user_username(){
    return ccpt_get_current_user_profile()['username'];
}


/**
 * Get current user avatar.
 * 
 * @return string
 */
function ccpt_get_current_user_avatar(){
    return ccpt_get_current_user_profile()['avatar'];
}


/**
 * Get courses ID started by current user.
 * 
 * @return string[]|integer[]
 */
function ccpt_get_current_user_courses(){
    return ccpt_get_current_user_profile()['courses'];
}


/**
 * Get user course data.
 * 
 * @param string|integer $user_id
 * @param string|integer $course_id
 * @param string
 */
function ccpt_get_user_course_data( $user_id = 0, $course_id ){
    if( $user_id === 0 )
        $user_id = get_current_user_id();

    $category = get_term( $course_id, 'article_category' );

    $readed_articles = ccpt_get_user_readed_course_articles( $user_id, $category->term_id );
    $progress = intval( round( count( $readed_articles ) / $category->count * 100 ) );
    $last_opened = intval( ccpt_get_user_last_opened_course_article( $user_id, $course_id ) );
    

    // Test

    $test_id = intval( get_term_meta( $course_id, 'ccpt_category_test', true ) );
    $test_result = ccpt_get_test_result( 0, $test_id );

    $status = 'unbegun';

    if( $progress > 0 && $progress <= 100 && $test_result['score'] < 85 ){
        $status = 'in-progress';
    }
    else if( $progress === 100 && $test_result['score'] > 85 ){
        $status = 'completed';
    }

    $output = array(
        'id'            => $course_id,
        'name'          => $category->name,
        'progress'      => $progress,
        'status'        => $status,
        'last_opened'   => $last_opened,
        'test'          => $test_id
    );

    return $output;
}


/**
 * Get current user course data.
 * 
 * @param string|integer $user_id
 * @param string|integer $course_id
 * @param string
 */
function ccpt_get_current_user_course_data( $course_id ){
    return ccpt_get_user_course_data( 0, $course_id );
}


/**
 * Get courses data for current user.
 * 
 * @return string[]|boolean
 */
function ccpt_get_current_user_courses_data(){
    $courses_id = ccpt_get_current_user_courses();

    if( empty( $courses_id ) )
        return false;

    $output = array();

    foreach( $courses_id as $course_id ){
        $output[$course_id] = ccpt_get_current_user_course_data( $course_id );
    }

    return $output;
}


/**
 * Get readed by user articles from specific course.
 * 
 * @param string|integer $user_id
 * @param string|integer $course_id
 * @return string[]|integer[]
 */
function ccpt_get_user_readed_course_articles( $user_id, $course_id ){
    $readed_articles = get_user_meta( $user_id, 'ccpt_readed_articles_in_course_' . $course_id, true );

    if( empty( $readed_articles ) )
        return [];

    return $readed_articles;
}


/**
 * Get readed by current user articles from specific course.
 * 
 * @param string|integer $course_id
 * @return string[]|integer[]
 */
function ccpt_get_current_user_readed_course_articles( $course_id ){
    $user_id = get_current_user_id();

    return ccpt_get_user_readed_course_articles( $user_id, $course_id );
}


/**
 * Check if user has a role.
 * 
 * @param int|WP_User $user
 * @param string $role
 * @return bool
 */
function ccpt_user_has_role( $user, $role ){
    if( !is_object( $user ) )
        $user = get_userdata( $user );

    if( !$user || !$user->exists() )
        return false;

    return in_array( $role, $user->roles, true );
}


/**
 * Check if current user has a role.
 * 
 * @param string $role
 * @return bool
 */
function ccpt_current_user_has_role( $role ){
    return ccpt_current_user_has_role( wp_get_current_user(), $role );
}


/**
 * Check is Facebook profile linked to user profile.
 * 
 * @param string $role
 * @return bool
 */
function ccpt_is_user_linked_facebook( $user_id ){
    return get_user_meta( $user_id, 'ccpt_facebook_id', true );
}


/**
 * Add course to current user courses list.
 * 
 * @param integer|string $course_id
 * @return boolean
 */
function ccpt_maybe_begin_course( $course_id ){
    $current_user_id = get_current_user_id();
    $courses = get_user_meta( $current_user_id, 'ccpt_started_courses', true );

    if( !empty( $courses ) && array_search( $course_id, $courses ) === false ){
        $courses[] = $course_id;

        return boolval( update_user_meta( $current_user_id, 'ccpt_started_courses', $courses ) );
    }
    else if( !empty( $courses ) && array_search( $course_id, $courses ) !== false ){
        return false;
    }
    else if( empty( $courses ) ){
        return boolval( update_user_meta( $current_user_id, 'ccpt_started_courses', [$course_id] ) );
    }
}


/**
 * Maybe add article to current user readed course articles list.
 * 
 * @param integer|string $course_id
 * @return boolean
 */
function ccpt_maybe_add_article_to_readed( $article_id ){
    $current_user_id = get_current_user_id();
    $course = ccpt_get_article_category( $article_id );
    $articles = get_user_meta( $current_user_id, 'ccpt_readed_articles_in_course_' . $course->term_id, true );

    if( !empty( $articles ) && array_search( $article_id, $articles ) === false ){
        $articles[] = $article_id;

        return boolval( update_user_meta( $current_user_id, 'ccpt_readed_articles_in_course_' . $course->term_id, $articles ) );
    }
    else if( !empty( $articles ) && array_search( $article_id, $articles ) !== false ){
        return false;
    }
    else if( empty( $articles ) ){
        return boolval( update_user_meta( $current_user_id, 'ccpt_readed_articles_in_course_' . $course->term_id, [$article_id] ) );
    }
}


/**
 * Maybe update user tests list.
 * 
 * @param string|integer $user_id
 * @param string|integer $test_id
 * @return boolean
 */
function ccpt_maybe_update_user_tests_list( $user_id = 0, $test_id ){
    if( $user_id === 0 )
        $user_id = get_current_user_id();
    
    $tests = get_user_meta( $user_id, 'ccpt_tests', true );

    if( !empty( $tests ) && array_search( $test_id, $tests ) === false ){
        $tests[] = $test_id;
        return boolval( update_user_meta( $user_id, 'ccpt_tests', $tests ) );
    }
    else if( !empty( $tests ) && array_search( $test_id, $tests ) !== false ){
        return false;
    }
    else if( empty( $tests ) ){
        return boolval( update_user_meta( $user_id, 'ccpt_tests', [$test_id] ) );
    }
}


/**
 * Get users test list.
 * 
 * @param string|integer $user_id
 * @return string[]
 */
function ccpt_get_user_tests( $user_id = 0 ){
    if( $user_id === 0 )
        $user_id = get_current_user_id();

    $tests = get_user_meta( $user_id, 'ccpt_tests', true );

    if( !empty( $tests ) )
        return $tests;

    return [];
}


/**
 * Update user last opened article in course.
 * 
 * @param string|integer $user_id
 * @param string|integer $article_id
 * @return boolean
 */
function ccpt_update_user_last_opened_course_article( $user_id, $article_id ){
    $course = ccpt_get_article_category( $article_id );

    return boolval( update_user_meta( $user_id, 'ccpt_last_opened_in_course_' . $course->term_id, $article_id ) );
}


/**
 * Update current user last opened article in course.
 * 
 * @param string|integer $user_id
 * @param string|integer $article_id
 * @return boolean
 */
function ccpt_update_current_user_last_opened_course_article( $article_id ){
    $user_id = get_current_user_id();
    $course = ccpt_get_article_category( $article_id );

    return boolval( update_user_meta( $user_id, 'ccpt_last_opened_in_course_' . $course->term_id, $article_id ) );
}


/**
 * Get user last opened article in course.
 * 
 * @param string|integer $user_id
 * @param string|integer $article_id
 * @return boolean
 */
function ccpt_get_user_last_opened_course_article( $user_id, $course_id ){
    return get_user_meta( $user_id, 'ccpt_last_opened_in_course_' . $course_id, true );
}


/**
 * Get current user last opened article in course.
 * 
 * @param string|integer $user_id
 * @param string|integer $article_id
 * @return boolean
 */
function ccpt_get_current_user_last_opened_course_article( $course_id ){
    $user_id = get_current_user_id();

    return ccpt_get_user_last_opened_course_article( $user_id, $course_id );
}


// Begin course for current user.

add_action( 'ccpt_before_articles_category', 'ccpt_begin_course' );

function ccpt_begin_course(){
    if( !empty( $_GET['begin_course'] ) ){
        $course_id = get_queried_object()->term_id;
        ccpt_maybe_begin_course( $course_id );
    }
}


// Add article to for current user.

add_action( 'ccpt_before_single_article', 'ccpt_add_article' );

function ccpt_add_article(){
    ccpt_update_current_user_last_opened_course_article( get_queried_object_id() );

    if( !empty( $_GET['prev_article'] ) ){
        ccpt_maybe_add_article_to_readed( $_GET['prev_article'] );
    }
}


// Block student from accessing WP dashboard.

add_action( 'init', 'ccpt_disable_student_wp_dashboard' );

function ccpt_disable_student_wp_dashboard(){
    if( is_admin() && is_user_logged_in() && !current_user_can( 'edit_posts' ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
        wp_safe_redirect( home_url() );
        exit;
    }
}


// Disable admin bar for student.

add_filter( 'show_admin_bar', 'ccpt_disable_admin_bar', 10, 1 );

function ccpt_disable_admin_bar( $show_admin_bar ){
    if( !current_user_can( 'edit_posts' ) ){
        $show_admin_bar = false;
    }

    return $show_admin_bar;
}