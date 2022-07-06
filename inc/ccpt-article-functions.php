<?php

/**
 * CeCrypto Article functions.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


/**
 * Get all articles.
 * 
 * @return WP_Post[]
 */
function ccpt_get_articles( $numberposts = -1, $ids = [] ){
    $args = array(
        'numberposts'   => $numberposts,
        'post_type'     => 'article'
    );

    if( !empty( $ids ) ){
        $args['post__in'] = $ids;
    }

    return get_posts( $args );
}


/**
 * Get articles category taxonomy query.
 * 
 * @param string|integer $course_id
 * @return WP_Query
 */
function ccpt_get_articles_category_query(){
    $args = array(
        'post_type'         => 'article',
        'posts_per_page'    => 2
    );

    if( isset( $_GET['page'] ) ){
        $args['paged'] = $_GET['page'];
    }

    if( isset( $_GET['time'] ) ){
        $values = json_decode( $_GET['time'] );

        $args['meta_query'] = [
            array(
                'key'       => '_reading_time',
                'value'     => $values,
                'compare'   => 'BETWEEN',
                'type'      => 'NUMERIC'
            )
        ];
    }

    $tax_query_filters = [
        array(
            'taxonomy'  => 'article_category',
            'terms'     => get_queried_object_id()
        )
    ];

    if( isset( $_GET['theme'] ) ){
        $tax_query_filters[] = array(
            'taxonomy'  => 'article_tag',
            'terms'     => explode( ',', $_GET['theme'] ),
            'operator'  => 'IN',
            'relation'  => 'OR'
        );
    }

    if( isset( $_GET['difficulty'] ) ){
        $tax_query_filters[] = array(
            'taxonomy'  => 'article_difficulty',
            'terms'     => explode( ',', $_GET['difficulty'] ),
            'operator'  => 'IN',
            'relation'  => 'OR'
        );
    }

    if( count( $tax_query_filters ) ){
        $args['tax_query'] = array(
            'relation'  => 'AND',
            $tax_query_filters
        );
    }

    $query = new WP_Query( $args );

    return $query;
}


/**
 * Get all articles array.
 * 
 * @return string[]
 */
function ccpt_get_articles_arr(){
    $articles = ccpt_get_articles();
    $output = array();

    foreach( $articles as $article ){
        $output[$article->ID] = $article->post_title;
    }

    return $output;
}


/**
 * Get all articles choices.
 * 
 * @return string[]
 */
function ccpt_get_articles_choices(){
    $articles = ccpt_get_articles_arr();
    $articles = array( '' => __( 'Оберіть статтю', 'ce-crypto' ) ) + $articles;

    return $articles;
}


/**
 * Get pinned article ids.
 * 
 * @return string[]
 */
function ccpt_get_pinned_article_ids(){
    $pinned = [
        get_theme_mod( 'articles_archive_pinned_first' ),
        get_theme_mod( 'articles_archive_pinned_second' ),
        get_theme_mod( 'articles_archive_pinned_third' )
    ];

    return array_filter( $pinned );
}


/**
 * Get pinned articles.
 * 
 * @return WP_Post[]|boolean
 */
function ccpt_get_pinned_articles(){
    $ids = ccpt_get_pinned_article_ids();

    if( empty( $ids ) ){
        return false;
    }

    return ccpt_get_articles( -1, $ids );
}


/**
 * Get recent articles.
 * 
 * @param int[]|string[] $exclude
 * @param int $numberposts
 * @param int|string $category_id
 * @return WP_Post[]
 */
function ccpt_get_recent_articles( $exclude = [], $numberposts = 5, $category_id = false ){
    $args = array(
        'post_type'     => 'article',
        'numberposts'   => $numberposts
    );

    if( !empty( $exclude ) ){
        $args['exclude'] = $exclude;
    }

    if( $category_id ){
        $args['tax_query'] = array(
            'taxonomy'  => 'article_category',
            'field'     => 'id',
            'terms'     => $category_id
        );
    }

    return wp_get_recent_posts( $args, 'OBJECT' );
}


/**
 * Get recent articles from category.
 * 
 * @param int|string $category_id
 * @param int $numberposts
 * @param int[]|string[] $exclude
 * @return WP_Post
 */
function ccpt_get_category_recent_articles( $category_id, $numberposts = 5, $exclude = [] ){
    return ccpt_get_recent_articles( $exclude, $numberposts, $category_id );
}


/**
 * Search articles by title.
 * 
 * @param string $needle
 * @param string|integer $numberposts
 * @return WP_Post[]
 */
function ccpt_search_articles_by_title( $needle, $numberposts = 5 ){
    $query = new WP_Query( array(
        'post_type'     => 'article',
        'search_title'  => $needle,
        'numberposts'   => $numberposts
    ) );

    write_log( $results );

    $output = [];

    if( $query->have_posts() ){
        while( $query->have_posts() ){
            $query->the_post();

            $output[] = array(
                'title'     => get_the_title(),
                'link'      => get_permalink( get_the_ID() )
            );
        }
    }

    wp_reset_postdata();

    return $output;
}


/**
 * Get most recent article from category.
 * 
 * @param int $category_id
 * @return WP_Post
 */
function ccpt_get_category_most_recent_article( $category_id ){
    return ccpt_get_category_recent_articles( $category_id, 1 )[0];
}


/**
 * Get article categories.
 * 
 * @return WP_Term[]
 */
function ccpt_get_article_categories( $hide_empty = true ){
    $args = array(
        'taxonomy'      => 'article_category',
        'hide_empty'    => $hide_empty
    );

    return get_terms( $args );
}


/**
 * Get courses and group them by alphabet.
 * 
 * @return string[]
 */
function ccpt_get_courses_filter_items(){
    $courses = ccpt_get_article_categories( false );

    $output = array();

    foreach( $courses as $course ){
        $output[ccpt_get_title_order_weight( $course->name )][] = $course->term_id;
    }

    return $output;
}


/**
 * Get all article tags.
 * 
 * @return WP_Term[]
 */
function ccpt_get_all_article_tags(){
    $args = array(
        'taxonomy'      => 'article_tag'
    );

    return get_terms( $args );
}


/**
 * Get all article difficulties.
 * 
 * @return WP_Term[]
 */
function ccpt_get_all_article_difficulties(){
    $args = array(
        'taxonomy'      => 'article_difficulty'
    );

    return get_terms( $args );
}


/**
 * Get article category.
 * 
 * @param string|integer $article_id
 * @return WP_Term
 */
function ccpt_get_article_category( $article_id ){
    return wp_get_post_terms( $article_id, 'article_category' )[0];
}


/**
 * Get article category name.
 * 
 * @param string|integer $term_id
 * @return string
 */
function ccpt_get_article_category_name( $term_id ){
    $article_category = get_term( $term_id );

    return $article_category->name;
}


/**
 * Get article category test ID.
 * 
 * @param string|integer $post_id
 * @return integer
 */
function ccpt_get_article_category_test( $post_id ){
    $terms = get_the_terms( $post_id, 'article_category' );

    return intval( get_term_meta( $terms[0]->term_id, 'ccpt_category_test', true ) );
}


/**
 * Check if course is started by user.
 * 
 * @param string|integer $user_id
 * @param string|integer $course_id
 * @return boolean
 */
function ccpt_is_course_in_progress( $user_id = 0, $course_id ){
    if( $user_id === 0 )
        $user_id = get_current_user_id();

    $user_courses = get_user_meta( $user_id, 'ccpt_started_courses', true );

    if( empty( $user_courses ) )
        return false;

    return array_search( $course_id, $user_courses ) !== false;
}


/**
 * Update article views.
 * 
 * @param string|integer $post_id
 * @return boolean
 */
function ccpt_update_article_views( $post_id = 0 ){
    if( $post_id === 0 )
        $post_id = get_the_ID();

    if( $post_id === false )
        return false;

    $views = get_post_meta( $post_id, 'ccpt_views', true );

    $is_updated = false;

    if( empty( $views ) ){
        $is_updated = boolval( update_post_meta( $post_id, 'ccpt_views', 1 ) );
    }
    else{
        $is_updated = boolval( update_post_meta( $post_id, 'ccpt_views', $views + 1 ) );
    }

    return $is_updated;
}


/**
 * Get article views.
 * 
 * @param string|integer $post_id
 * @return integer
 */
function ccpt_get_views( $post_id = 0 ){
    if( $post_id === 0 )
        $post_id = get_the_ID();

    if( $post_id === false )
        return false;

    $views = get_post_meta( $post_id, 'ccpt_views', true );

    return $views ? $views : 0;
}


/**
 * Update article likes.
 * 
 * @param string|integer $post_id
 * @return boolean
 */
function ccpt_update_article_likes( $post_id = 0 ){
    if( $post_id === 0 )
        $post_id = get_the_ID();

    if( $post_id === false )
        return false;

    $likes = get_post_meta( $post_id, 'ccpt_likes', true );

    $is_updated = false;

    if( empty( $likes ) ){
        $is_updated = boolval( update_post_meta( $post_id, 'ccpt_likes', 1 ) );
    }
    else{
        $is_updated = boolval( update_post_meta( $post_id, 'ccpt_likes', $likes + 1 ) );
    }

    return $is_updated;
}


/**
 * Get article likes.
 * 
 * @param string|integer $post_id
 * @return integer
 */
function ccpt_get_likes( $post_id = 0 ){
    if( $post_id === 0 )
        $post_id = get_the_ID();

    if( $post_id === false )
        return false;

    $likes = get_post_meta( $post_id, 'ccpt_likes', true );

    return $likes ? $likes : 0;
}


// Add article post meta on creating article.

add_action( 'save_post_article', 'ccpt_save_post_article', 10, 3 );

function ccpt_save_post_article( $post_id, $post, $update ){
    if( !$update ){
        update_post_meta( $post_id, 'ccpt_views', 0 );
        update_post_meta( $post_id, 'ccpt_likes', 0 );
    }
}


// Add test select for article_category taxonomy terms.

add_action( 'article_category_add_form_fields', 'ccpt_article_category_add_form_fields' );

function ccpt_article_category_add_form_fields( $taxonomy ){

    ?>

    <div class="form-field">
        <label for="category-test"><?=__( 'Тест категорії', 'ce-crypto' ); ?></label>
        <select name="category_test" id="category-test" style="width: 100%;">
            <?=ccpt_get_test_options_html(); ?>
        </select>
    </div>

    <?php

}

add_action( 'article_category_edit_form_fields', 'ccpt_article_category_edit_form_fields', 10, 2 );

function ccpt_article_category_edit_form_fields( $term, $taxonomy ){
    $value = get_term_meta( $term->term_id, 'ccpt_category_test', true );

    ?>
    
    <tr class="form-field">
        <th>
            <label for="category-test"><?=__( 'Тест категорії', 'ce-crypto' ); ?></label>
        </th>
        
        <td>
            <select name="category_test" id="category-test" style="width: 100%;">
                <?=ccpt_get_test_options_html( $value ); ?>
            </select>
        </td>
    </tr>

    <?php

}

add_action( 'created_article_category', 'ccpt_save_article_category_fields' );
add_action( 'edited_article_category', 'ccpt_save_article_category_fields' );

function ccpt_save_article_category_fields( $term_id ){
    $test_id = sanitize_text_field( $_POST['category_test'] );

    update_post_meta( $test_id, 'ccpt_course', $term_id );
    update_term_meta( $term_id, 'ccpt_category_test', $test_id );
}


// Increase views count

add_action( 'wp', 'ccpt_increase_article_views' );

function ccpt_increase_article_views(){
    if( is_singular( 'article' ) && is_user_logged_in() ){
        ccpt_update_article_views();
    }
}


// Maybe unlock course test.

add_action( 'wp', 'ccpt_maybe_unlock_test' );

function ccpt_maybe_unlock_test(){
    if( is_tax( 'article_category' ) && get_query_var( 'unlock_test' ) ){
        ccpt_unlock_test();
    }
}


// Articles archive filters query.

add_action( 'pre_get_posts', 'ccpt_articles_archive_filters_query' );

function ccpt_articles_archive_filters_query( $query ){
    if( !is_admin() && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === 'article' ){
        if( isset( $_GET['page'] ) ){
            $query->set( 'paged', $_GET['page'] );
        }
    
        if( isset( $_GET['time'] ) ){
            $values = json_decode( $_GET['time'] );
    
            $query->set( 'meta_query', [
                array(
                    'key'       => '_reading_time',
                    'value'     => $values,
                    'compare'   => 'BETWEEN',
                    'type'      => 'NUMERIC'
                )
            ] );
        }
    
        $tax_query_filters = [];
    
        if( isset( $_GET['theme'] ) ){
            $tax_query_filters[] = array(
                'taxonomy'  => 'article_tag',
                'terms'     => explode( ',', $_GET['theme'] ),
                'operator'  => 'IN',
                'relation'  => 'OR'
            );
        }
    
        if( isset( $_GET['difficulty'] ) ){
            $tax_query_filters[] = array(
                'taxonomy'  => 'article_difficulty',
                'terms'     => explode( ',', $_GET['difficulty'] ),
                'operator'  => 'IN',
                'relation'  => 'OR'
            );
        }
    
        if( count( $tax_query_filters ) ){
            $query->set( 'tax_query', array(
                'relation'  => 'AND',
                $tax_query_filters
            ) );
        }
    }
}