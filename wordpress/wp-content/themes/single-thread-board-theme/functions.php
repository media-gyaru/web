<?php

if (!defined('ABSPATH')) {
    exit;
}

define('STB_THEME_VERSION', '1.0.0');
define('STB_POST_TYPE', 'stb_board_post');
define('STB_META_NAME', '_stb_author_name');
define('STB_META_IMAGE', '_stb_image_id');

function stb_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'single-thread-board'),
    ));
}
add_action('after_setup_theme', 'stb_theme_setup');

function stb_register_post_type() {
    register_post_type(STB_POST_TYPE, array(
        'labels' => array(
            'name' => __('Board Posts', 'single-thread-board'),
            'singular_name' => __('Board Post', 'single-thread-board'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-format-chat',
        'supports' => array('editor'),
        'capability_type' => 'post',
    ));
}
add_action('init', 'stb_register_post_type');

function stb_enqueue_assets() {
    wp_enqueue_style('stb-style', get_stylesheet_uri(), array(), STB_THEME_VERSION);
    wp_enqueue_script('stb-board', get_template_directory_uri() . '/assets/board.js', array(), STB_THEME_VERSION, true);
}
add_action('wp_enqueue_scripts', 'stb_enqueue_assets');

function stb_get_board_count() {
    return (int) wp_count_posts(STB_POST_TYPE)->publish;
}

function stb_handle_board_submission() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['stb_board_action'])) {
        return;
    }

    if (!isset($_POST['stb_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['stb_nonce'])), 'stb_submit_board_post')) {
        stb_redirect_with_status('error');
    }

    $author_name = isset($_POST['stb_author_name']) ? sanitize_text_field(wp_unslash($_POST['stb_author_name'])) : '';
    $message = isset($_POST['stb_message']) ? wp_kses_post(wp_unslash($_POST['stb_message'])) : '';
    $image_data = isset($_POST['stb_canvas_image']) ? trim(wp_unslash($_POST['stb_canvas_image'])) : '';

    if ($author_name === '') {
        $author_name = __('Anonymous', 'single-thread-board');
    }

    if (trim(wp_strip_all_tags($message)) === '' && $image_data === '') {
        stb_redirect_with_status('empty');
    }

    $post_id = wp_insert_post(array(
        'post_type' => STB_POST_TYPE,
        'post_status' => 'publish',
        'post_content' => $message,
    ), true);

    if (is_wp_error($post_id)) {
        stb_redirect_with_status('error');
    }

    update_post_meta($post_id, STB_META_NAME, $author_name);

    if ($image_data !== '') {
        $image_id = stb_save_canvas_image($image_data, $post_id);

        if ($image_id) {
            update_post_meta($post_id, STB_META_IMAGE, $image_id);
        }
    }

    stb_redirect_with_status('posted');
}
add_action('template_redirect', 'stb_handle_board_submission');

function stb_redirect_with_status($status) {
    $url = remove_query_arg('board_status');
    $url = add_query_arg('board_status', rawurlencode($status), $url);
    wp_safe_redirect($url . '#board-feed');
    exit;
}

function stb_save_canvas_image($image_data, $post_id) {
    if (!preg_match('/^data:image\/png;base64,/', $image_data)) {
        return 0;
    }

    $encoded = substr($image_data, strpos($image_data, ',') + 1);
    $decoded = base64_decode($encoded, true);

    if (!$decoded || strlen($decoded) > 2 * MB_IN_BYTES) {
        return 0;
    }

    if (!function_exists('wp_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!function_exists('wp_generate_attachment_metadata')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $upload = wp_upload_bits('board-drawing-' . $post_id . '-' . time() . '.png', null, $decoded);

    if (!empty($upload['error'])) {
        return 0;
    }

    $attachment_id = wp_insert_attachment(array(
        'post_mime_type' => 'image/png',
        'post_title' => sprintf(__('Board drawing %d', 'single-thread-board'), $post_id),
        'post_content' => '',
        'post_status' => 'inherit',
    ), $upload['file'], $post_id);

    if (is_wp_error($attachment_id)) {
        return 0;
    }

    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $metadata);

    return (int) $attachment_id;
}

function stb_status_message() {
    if (!isset($_GET['board_status'])) {
        return '';
    }

    $status = sanitize_key(wp_unslash($_GET['board_status']));

    if ($status === 'posted') {
        return '<p class="board-message">' . esc_html__('Posted.', 'single-thread-board') . '</p>';
    }

    if ($status === 'empty') {
        return '<p class="board-message board-message--error">' . esc_html__('Write a message or draw something before posting.', 'single-thread-board') . '</p>';
    }

    return '<p class="board-message board-message--error">' . esc_html__('Could not post. Please try again.', 'single-thread-board') . '</p>';
}
