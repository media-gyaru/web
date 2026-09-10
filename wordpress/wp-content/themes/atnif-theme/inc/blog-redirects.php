<?php
/**
 * ブログURLの履歴保存と解決。
 *
 * @package atnif
 */

if (!defined('ABSPATH')) {
    exit;
}

// 現在のスラッグ、旧スラッグ、旧形式の投稿IDの順に解決する。
function atnif_resolve_blog_post_id($slug) {
    if ('' === $slug) {
        return 0;
    }

    // 配列指定により、同名の添付ファイルが検索結果に混ざるのを防ぐ。
    $post = get_page_by_path($slug, OBJECT, array('post'));

    if ($post instanceof WP_Post) {
        return (int) $post->ID;
    }

    $post_ids = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'meta_key' => '_wp_old_slug',
        'meta_value' => $slug,
        'posts_per_page' => 2,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'no_found_rows' => true,
    ));

    // 同じ旧URLが複数の記事に残っている場合、転送先を推測しない。
    if (count($post_ids) > 1) {
        return 0;
    }

    if ($post_ids) {
        return (int) $post_ids[0];
    }

    if (ctype_digit($slug) && (int) $slug > 0) {
        $post = get_post((int) $slug);

        if ($post instanceof WP_Post && 'post' === $post->post_type) {
            return (int) $post->ID;
        }
    }

    return 0;
}

// WordPress標準では記録されない予約中・下書き中の変更も履歴に残す。
function atnif_track_blog_slug_changes($post_id, $post, $post_before) {
    $statuses = array('draft', 'pending', 'future', 'publish');

    if ('post' !== $post->post_type || $post->post_name === $post_before->post_name
        || !in_array($post->post_status, $statuses, true)
        || !in_array($post_before->post_status, $statuses, true)) {
        return;
    }

    $old_slugs = get_post_meta($post_id, '_wp_old_slug');

    if ('' !== $post_before->post_name && !in_array($post_before->post_name, $old_slugs, true)) {
        add_post_meta($post_id, '_wp_old_slug', $post_before->post_name);
    }

    delete_post_meta($post_id, '_wp_old_slug', $post->post_name);
}
add_action('post_updated', 'atnif_track_blog_slug_changes', 13, 3);

// 既にSNSで共有された既知の旧URLを一度だけ復旧する。接尾辞からの推測は行わない。
function atnif_restore_shared_blog_slug() {
    if (get_option('atnif_shared_blog_slug_restored')) {
        return;
    }

    $post = get_page_by_path('2-2', OBJECT, array('post'));

    if (!$post instanceof WP_Post || !in_array($post->post_status, array('publish', 'future'), true)) {
        return;
    }

    if (!in_array('2', get_post_meta($post->ID, '_wp_old_slug'), true)
        && !add_post_meta($post->ID, '_wp_old_slug', '2')) {
        return;
    }

    update_option('atnif_shared_blog_slug_restored', '1');
}
add_action('init', 'atnif_restore_shared_blog_slug');

// 標準の旧スラッグ転送は公開状態・履歴の競合を確認しないため、ブログでは独自処理に統一する。
function atnif_disable_default_old_blog_slug_redirect($post_id) {
    return atnif_is_blog_post_request() ? 0 : $post_id;
}
add_filter('old_slug_redirect_post_id', 'atnif_disable_default_old_blog_slug_redirect');
