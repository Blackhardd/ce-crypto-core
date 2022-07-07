<?php

/**
 * Custom post types class.
 * 
 * @package CeCrypto Core
 * @since 0.0.1
 */


if( !defined( 'ABSPATH' ) )
    exit;


class CCPT_Post_Types {
    /**
     * Hooking methods.
     */
    public static function init(){
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
    }


    /**
     * Registering post types.
     */
    public static function register_post_types(){
        if( post_type_exists( 'article' ) )
            return;

        register_post_type( 'article', array(
            'labels'                => array(
                'name'                  => __( 'Статті', 'ce-crypto' ),
                'singular_name'         => __( 'Стаття', 'ce-crypto' ),
                'all_items'             => __( 'Усі статті', 'ce-crypto' ),
                'menu_name'             => _x( 'Статті', 'Ім`я пункту меню', 'ce-crypto' ),
                'add_new'               => __( 'Додати нову', 'ce-crypto' ),
                'add_new_item'          => __( 'Додати нову статтю', 'ce-crypto' ),
                'edit'                  => __( 'Редагувати', 'ce-crypto' ),
				'edit_item'             => __( 'Редагувати статтю', 'ce-crypto' ),
				'new_item'              => __( 'Нова стаття', 'ce-crypto' ),
				'view_item'             => __( 'Переглянути статтю', 'ce-crypto' ),
				'view_items'            => __( 'Переглядати статті', 'ce-crypto' ),
				'search_items'          => __( 'Шукати статті', 'ce-crypto' ),
				'not_found'             => __( 'Статтей не знайдено', 'ce-crypto' ),
				'not_found_in_trash'    => __( 'Статтей не знайдено у кошику', 'ce-crypto' ),
				'parent'                => __( 'Батьківська стаття', 'ce-crypto' ),
				'featured_image'        => __( 'Зображення статті', 'ce-crypto' ),
				'set_featured_image'    => __( 'Встановити зображення статті', 'ce-crypto' ),
				'remove_featured_image' => __( 'Видалити зображення статті', 'ce-crypto' ),
				'use_featured_image'    => __( 'Використати як зображення статті', 'ce-crypto' ),
				'insert_into_item'      => __( 'Вставити у статтю', 'ce-crypto' ),
				'uploaded_to_this_item' => __( 'Завантажити файли у цю статтю', 'ce-crypto' ),
				'filter_items_list'     => __( 'Фільтрувати статті', 'ce-crypto' ),
				'items_list_navigation' => __( 'Навігація по статтям', 'ce-crypto' ),
				'items_list'            => __( 'Список статтей', 'ce-crypto' ),
				'item_link'             => __( 'Посилання Статті', 'ce-crypto' ),
				'item_link_description' => __( 'Посилання на статтю.', 'ce-crypto' )
            ),
            'description'           => __( 'Тут ви можете переглянути усі статті.', 'ce-crypto' ),
            'public'                => true,
            'show_ui'               => true,
            'menu_icon'             => 'dashicons-text-page',
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => false,
            'hierarchical'          => false,
            'rewrite'               => true,
            'query_var'             => true,
            'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields' ),
            'has_archive'           => true,
            'show_in_nav_menus'     => true,
            'show_in_rest'          => true
        ) );

        register_post_type( 'test', array(
            'labels'                => array(
                'name'                  => __( 'Тести', 'ce-crypto' ),
                'singular_name'         => __( 'Тест', 'ce-crypto' ),
                'all_items'             => __( 'Усі тести', 'ce-crypto' ),
                'menu_name'             => _x( 'Тести', 'Ім`я пункту меню', 'ce-crypto' ),
                'add_new'               => __( 'Додати новий', 'ce-crypto' ),
                'add_new_item'          => __( 'Додати новий тест', 'ce-crypto' ),
                'edit'                  => __( 'Редагувати', 'ce-crypto' ),
				'edit_item'             => __( 'Редагувати тест', 'ce-crypto' ),
				'new_item'              => __( 'Новий тест', 'ce-crypto' ),
				'view_item'             => __( 'Переглянути тест', 'ce-crypto' ),
				'view_items'            => __( 'Переглядати тести', 'ce-crypto' ),
				'search_items'          => __( 'Шукати тести', 'ce-crypto' ),
				'not_found'             => __( 'Тестів не знайдено', 'ce-crypto' ),
				'not_found_in_trash'    => __( 'Тестів не знайдено у кошику', 'ce-crypto' ),
				'parent'                => __( 'Батьківський тест', 'ce-crypto' ),
				'featured_image'        => __( 'Зображення теста', 'ce-crypto' ),
				'set_featured_image'    => __( 'Встановити зображення теста', 'ce-crypto' ),
				'remove_featured_image' => __( 'Видалити зображення теста', 'ce-crypto' ),
				'use_featured_image'    => __( 'Використати як зображення теста', 'ce-crypto' ),
				'insert_into_item'      => __( 'Вставити у тест', 'ce-crypto' ),
				'uploaded_to_this_item' => __( 'Завантажити файли у цей тест', 'ce-crypto' ),
				'filter_items_list'     => __( 'Фільтрувати тести', 'ce-crypto' ),
				'items_list_navigation' => __( 'Навігація по тестам', 'ce-crypto' ),
				'items_list'            => __( 'Список тестів', 'ce-crypto' ),
				'item_link'             => __( 'Посилання Теста', 'ce-crypto' ),
				'item_link_description' => __( 'Посилання на тест.', 'ce-crypto' )
            ),
            'description'           => __( 'Тут ви можете переглянути усі тести.', 'ce-crypto' ),
            'public'                => true,
            'show_ui'               => true,
            'menu_icon'             => 'dashicons-saved',
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => false,
            'hierarchical'          => false,
            'rewrite'               => true,
            'query_var'             => true,
            'supports'              => array( 'title' ),
            'has_archive'           => false,
            'show_in_nav_menus'     => true,
            'show_in_rest'          => true
        ) );

        register_post_type( 'term', array(
            'labels'                => array(
                'name'                  => __( 'Терміни', 'ce-crypto' ),
                'singular_name'         => __( 'Термін', 'ce-crypto' ),
                'all_items'             => __( 'Усі терміни', 'ce-crypto' ),
                'menu_name'             => _x( 'Терміни', 'Ім`я пункту меню', 'ce-crypto' ),
                'add_new'               => __( 'Додати новий', 'ce-crypto' ),
                'add_new_item'          => __( 'Додати новий термін', 'ce-crypto' ),
                'edit'                  => __( 'Редагувати', 'ce-crypto' ),
				'edit_item'             => __( 'Редагувати термін', 'ce-crypto' ),
				'new_item'              => __( 'Новий термін', 'ce-crypto' ),
				'view_item'             => __( 'Переглянути термін', 'ce-crypto' ),
				'view_items'            => __( 'Переглядати терміни', 'ce-crypto' ),
				'search_items'          => __( 'Шукати терміни', 'ce-crypto' ),
				'not_found'             => __( 'Термінів не знайдено', 'ce-crypto' ),
				'not_found_in_trash'    => __( 'Термінів не знайдено у кошику', 'ce-crypto' ),
				'parent'                => __( 'Батьківський термін', 'ce-crypto' ),
				'featured_image'        => __( 'Зображення терміна', 'ce-crypto' ),
				'set_featured_image'    => __( 'Встановити зображення терміна', 'ce-crypto' ),
				'remove_featured_image' => __( 'Видалити зображення терміна', 'ce-crypto' ),
				'use_featured_image'    => __( 'Використати як зображення терміна', 'ce-crypto' ),
				'insert_into_item'      => __( 'Вставити у термін', 'ce-crypto' ),
				'uploaded_to_this_item' => __( 'Завантажити файли у цей термін', 'ce-crypto' ),
				'filter_items_list'     => __( 'Фільтрувати терміни', 'ce-crypto' ),
				'items_list_navigation' => __( 'Навігація по термінам', 'ce-crypto' ),
				'items_list'            => __( 'Список термінів', 'ce-crypto' ),
				'item_link'             => __( 'Посилання Терміна', 'ce-crypto' ),
				'item_link_description' => __( 'Посилання на термін.', 'ce-crypto' )
            ),
            'description'           => __( 'Тут ви можете переглянути усі терміни.', 'ce-crypto' ),
            'public'                => true,
            'show_ui'               => true,
            'menu_icon'             => 'dashicons-editor-textcolor',
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => false,
            'hierarchical'          => false,
            'rewrite'               => true,
            'query_var'             => true,
            'supports'              => array( 'title', 'editor', 'custom-fields' ),
            'has_archive'           => true,
            'show_in_nav_menus'     => true,
            'show_in_rest'          => true
        ) );
    }


    /**
     * Registering taxonomies.
     */
    public static function register_taxonomies(){
        if( taxonomy_exists( 'article_category' ) )
            return;

        register_taxonomy( 'article_category', 'article', array(
            'labels'                => array(
                'name'                          => _x( 'Категорії', 'Ім`я таксономії', 'ce-crypto' ),
                'singular_name'                 => _x( 'Категорія', 'Одиночне ім`я таксономії', 'ce-crypto' ),
                'search_items'                  => __( 'Шукати Категорії', 'ce-crypto' ),
                'popular_items'                 => __( 'Популярні Категорії', 'ce-crypto' ),
                'all_items'                     => __( 'Всі Категорії', 'ce-crypto' ),
                'parent_item'                   => null,
                'parent_item_colon'             => null,
                'edit_item'                     => __( 'Редагувати Категорію', 'ce-crypto' ), 
                'update_item'                   => __( 'Оновити Категорію', 'ce-crypto' ),
                'add_new_item'                  => __( 'Додати нову Категорію', 'ce-crypto' ),
                'new_item_name'                 => __( 'Нове ім`я категорії', 'ce-crypto' ),
                'separate_items_with_commas'    => __( 'Розділіть категорії комами', 'ce-crypto' ),
                'add_or_remove_items'           => __( 'Додати або видалити категорії', 'ce-crypto' ),
                'choose_from_most_used'         => __( 'Обрати серед популярних категорій', 'ce-crypto' ),
                'menu_name'                     => __( 'Категорії', 'ce-crypto' )
            ),
            'hierarchical'          => true,
            'public'                => true,
            'show_in_rest'          => true
        ) );

        register_taxonomy( 'article_tag', 'article', array(
            'labels'                => array(
                'name'                          => _x( 'Теги', 'Ім`я таксономії', 'ce-crypto' ),
                'singular_name'                 => _x( 'Тег', 'Одиночне ім`я таксономії', 'ce-crypto' ),
                'search_items'                  => __( 'Шукати Теги', 'ce-crypto' ),
                'popular_items'                 => __( 'Популярні Теги', 'ce-crypto' ),
                'all_items'                     => __( 'Всі Теги', 'ce-crypto' ),
                'parent_item'                   => null,
                'parent_item_colon'             => null,
                'edit_item'                     => __( 'Редагувати Тег', 'ce-crypto' ), 
                'update_item'                   => __( 'Оновити Тег', 'ce-crypto' ),
                'add_new_item'                  => __( 'Додати новий Тег', 'ce-crypto' ),
                'new_item_name'                 => __( 'Нове ім`я тега', 'ce-crypto' ),
                'separate_items_with_commas'    => __( 'Розділіть теги комами', 'ce-crypto' ),
                'add_or_remove_items'           => __( 'Додати або видалити теги', 'ce-crypto' ),
                'choose_from_most_used'         => __( 'Обрати серед популярних тегів', 'ce-crypto' ),
                'menu_name'                     => __( 'Теги', 'ce-crypto' )
            ),
            'hierarchical'          => true,
            'public'                => true,
            'show_in_rest'          => true
        ) );
    }
}


CCPT_Post_Types::init();