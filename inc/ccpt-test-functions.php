<?php

/**
 * CeCrypto Core test functions.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


/**
 * Get all tests.
 * 
 * @return WP_Post[]
 */
function ccpt_get_tests( $numberposts = -1 ){
    $args = array(
        'numberposts'   => $numberposts,
        'post_type'     => 'test'
    );

    return get_posts( $args );
}


/**
 * Get test choices.
 * 
 * @return string[]
 */
function ccpt_get_test_choices(){
    $tests = ccpt_get_tests();

    $output = array();

    foreach( $tests as $test ){
        $output[$test->ID] = $test->post_title;
    }

    return $output;
}


/**
 * Get test options HTML.
 * 
 * @return string
 */
function ccpt_get_test_options_html( $value = false ){
    $tests = ccpt_get_test_choices();

    $html = '';

    if( !$value ){
        $html .= '<option value="" selected>' . __( 'Оберіть тест зі списку', 'ce-crypto' ) . '</option>';
    }
    else{
        $html .= '<option value="">' . __( 'Оберіть тест зі списку', 'ce-crypto' ) . '</option>';
    }

    foreach( $tests as $id => $title ){
        $is_selected = $value && $value == $id ? 'selected' : '';
        $html .= "<option value='{$id}' {$is_selected}>{$title}</option>";
    }

    return $html;
}


/**
 * Get test course name.
 * 
 * @param string|integer $test_id
 * @return string
 */
function ccpt_get_test_course_name( $test_id ){
    $course_id = get_post_meta( $test_id, 'ccpt_course', true );

    return ccpt_get_article_category_name( $course_id );
}


/**
 * Get test course ID.
 * 
 * @param string|integer $test_id
 * @return boolean|integer
 */
function ccpt_get_test_course_id( $test_id ){
    if( !empty( $course_id = get_post_meta( $test_id, 'ccpt_course', true ) ) ){
        return $course_id;
    }

    return false;
}


/**
 * Extract correct answers from test data.
 * 
 * @param mixed[]
 * @return integer[]
 */
function ccpt_extract_correct_answers( $data ){
    $output_data = [];

    foreach( $data as $question_key => $question ){
        foreach( $question['answers'] as $answer_key => $answer ){
            if( $answer['is_correct'] ){
                $output_data[$question_key][] = $answer_key;
            }
        }
    }

    return $output_data;
}


/**
 * Get test result.
 * 
 * @param string|integer $user_id
 * @param string|integer $test_id
 * @return string[]|boolean
 */
function ccpt_get_test_result( $user_id = 0, $test_id = 0 ){
    if( $user_id === 0 )
        $user_id = get_current_user()->ID;

    if( $test_id === 0 && is_singular( 'test' ) )
        $test_id = get_the_ID();

    $result = get_user_meta( $user_id, 'ccpt_test_result_' . $test_id, true );
    $score = get_user_meta( $user_id, 'ccpt_test_score_' . $test_id, true );

    if( empty( $result ) && empty( $score ) ){
        return false;
    }

    return array(
        'course'    => ccpt_get_test_course_name( $test_id ),
        'course_id' => ccpt_get_test_course_id( $test_id ),
        'result'    => $result,
        'score'     => $score
    );
}


/**
 * Save test result.
 * 
 * @param string|integer $user_id
 * @param string|integer $test_id
 * @param boolean[] $result
 * @return boolean
 */
function ccpt_save_test_result( $user_id = 0, $test_id, $result ){
    if( $user_id === 0 )
        $user_id = get_current_user()->ID;

    $is_user_tests_list_updated = ccpt_maybe_update_user_tests_list( 0, $test_id );
    $is_test_score_updated = boolval( update_user_meta( $user_id, 'ccpt_test_score_' . $test_id, $result['score'] ) );
    $is_test_result_updated = boolval( update_user_meta( $user_id, 'ccpt_test_result_' . $test_id, $result['result'] ) );

    return $is_user_tests_list_updated && $is_test_score_updated && $is_test_result_updated;
}


/**
 * Lock test passing for user.
 * 
 * @param string|integer $user_id
 * @param string|integer $test_id
 * @return boolean
 */
function ccpt_lock_test( $user_id = 0, $test_id = 0 ){
    if( $user_id === 0 )
        $user_id = get_current_user()->ID;

    if( $test_id === 0 )
        $test_id = get_the_ID();

    return boolval( update_user_meta( $user_id, 'ccpt_test_status_' . $test_id, 'locked' ) );
}


/**
 * Unlock test passing for user.
 * 
 * @param string|integer $user_id
 * @param string|integer $test_id
 * @return boolean
 */
function ccpt_unlock_test( $user_id = 0, $test_id = 0 ){
    if( $user_id === 0 )
        $user_id = get_current_user()->ID;

    if( $test_id === 0 && !is_tax( 'article_category' ) ){
        $test_id = get_the_ID();
    }
    else if( $test_id === 0 && is_tax( 'article_category' ) ){
        $test_id = get_term_meta( get_queried_object()->term_id, 'ccpt_category_test', true );
    }
        

    return boolval( update_user_meta( $user_id, 'ccpt_test_status_' . $test_id, 'open' ) );
}


/**
 * Check if test is locked.
 * 
 * @param string|integer $user_id
 * @param string|integer $test_id
 * @return boolean
 */
function ccpt_is_test_locked( $user_id = 0, $test_id = 0 ){
    if( $user_id === 0 )
        $user_id = get_current_user()->ID;

    if( $test_id === 0 )
        $test_id = get_the_ID();

    $status = get_user_meta( $user_id, 'ccpt_test_status_' . $test_id, true );

    if( !empty( $status ) && $status === 'locked' ){
        return true;
    }

    return false;
}


// Restrict access to test page only for logged in users.

add_action( 'template_redirect', 'ccpt_restrict_access_to_test_page' );

function ccpt_restrict_access_to_test_page(){
    if( is_singular( 'test' ) && !is_user_logged_in() ){
        wp_safe_redirect( home_url() );
        exit;
    }
}


// Submit test.

add_action( 'wp', 'ccpt_submit_test' );

function ccpt_submit_test(){
    if( is_singular( 'test' ) && is_user_logged_in() && !empty( $_POST['action'] ) && $_POST['action'] === 'submit_test' ){
        $test_id = get_the_ID();
        $correct_answers = ccpt_extract_correct_answers( carbon_get_the_post_meta( 'questions' ) );
        $user_answers = json_decode( $_POST['answers'] );

        $result = [];
        $correct_answers_count = 0;

        foreach( $correct_answers as $question_key => $question_answers ){
            foreach( $question_answers as $key => $answer ){
                $result[$question_key][$key] = $user_answers[$question_key][$key] === $answer;
            }
        }

        $processed_result = [];

        foreach( $result as $key => $item ){
            $is_correct = true;

            foreach( $item as $answer ){
                if( $answer === false ){
                    $is_correct = false;
                }
            }

            if( $is_correct ){
                $correct_answers_count++;
            }

            $processed_result[$key] = $is_correct;
        }

        $score = round( $correct_answers_count / ( count( $processed_result ) / 100 ) );

        $data = array(
            'result'    => $processed_result,
            'score'     => $score
        );

        ccpt_save_test_result( 0, $test_id, $data );

        if( $score > 85 ){
            wp_safe_redirect( get_permalink( $test_id ) );
        }
        else if( get_query_var( 'second_attempt' ) ){
            ccpt_lock_test();

            wp_safe_redirect( get_permalink( $test_id ) );
        }
        else{
            wp_safe_redirect( add_query_arg( 'second_attempt', true, get_permalink( $test_id ) ) );
        }

        exit;
    }
}