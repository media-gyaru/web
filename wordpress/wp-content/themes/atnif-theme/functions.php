<?php

if (!defined('ABSPATH')) {
    exit;
}

define('ATNIF_THEME_VERSION', '1.1.0');

define('ATNIF_REWRITE_VERSION', '6');
define('ATNIF_CONTENT_CLEANUP_VERSION', '1');
define('ATNIF_CANONICAL_ORIGIN', 'https://atonif.com');

require_once get_template_directory() . '/inc/blog-redirects.php';

// 公開URLをHTTPSへ統一し、不要なサンプルページはトップへ転送する
function atnif_redirect_legacy_urls() {
    if (wp_doing_cron() || (defined('WP_CLI') && WP_CLI)) {
        return;
    }

    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) wp_unslash($_SERVER['HTTP_HOST'])) : '';
    $host = preg_replace('/:\d+$/', '', $host);

    if ('atonif.com' !== $host) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $request_path = (string) parse_url($request_uri, PHP_URL_PATH);
    $forwarded_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        ? strtolower(trim(explode(',', (string) wp_unslash($_SERVER['HTTP_X_FORWARDED_PROTO']))[0]))
        : '';
    $is_https = is_ssl() || 'https' === $forwarded_proto;

    if ('/sample-page' === rtrim($request_path, '/')) {
        wp_redirect(ATNIF_CANONICAL_ORIGIN . '/', 301, 'atnif-theme');
        exit;
    }

    if (!$is_https) {
        $safe_request_uri = wp_validate_redirect($request_uri, '/');
        wp_redirect(ATNIF_CANONICAL_ORIGIN . $safe_request_uri, 301, 'atnif-theme');
        exit;
    }
}
add_action('wp_loaded', 'atnif_redirect_legacy_urls', 0);

// WordPressが.htaccessを更新するときも、PHPより前に同じ301転送を適用する
function atnif_prepend_canonical_rewrite_rules($rules) {
    $canonical_rules = "RewriteCond %{HTTP_HOST} ^atonif\\.com$ [NC]\n"
        . "RewriteRule ^sample-page/?$ https://atonif.com/ [R=301,L]\n"
        . "RewriteCond %{HTTP_HOST} ^atonif\\.com$ [NC]\n"
        . "RewriteCond %{HTTPS} !=on\n"
        . "RewriteCond %{HTTP:X-Forwarded-Proto} !^https$ [NC]\n"
        . "RewriteRule ^ https://atonif.com%{REQUEST_URI} [R=301,L]\n";

    return $canonical_rules . $rules;
}
add_filter('mod_rewrite_rules', 'atnif_prepend_canonical_rewrite_rules');

// 初期作成されたサンプルページを一度だけゴミ箱へ移動する
function atnif_cleanup_sample_page_once() {
    if (get_option('atnif_content_cleanup_version') === ATNIF_CONTENT_CLEANUP_VERSION) {
        return;
    }

    $sample_page = get_page_by_path('sample-page', OBJECT, 'page');

    if ($sample_page instanceof WP_Post && 'trash' !== $sample_page->post_status) {
        $trashed_post = wp_trash_post($sample_page->ID);

        if (!$trashed_post) {
            return;
        }
    }

    update_option('atnif_content_cleanup_version', ATNIF_CONTENT_CLEANUP_VERSION);
}
add_action('init', 'atnif_cleanup_sample_page_once', 30);

// 削除処理前でもサンプルページを標準サイトマップへ掲載しない
function atnif_exclude_sample_page_from_sitemap($query_args, $post_type) {
    if ('page' !== $post_type) {
        return $query_args;
    }

    $sample_page = get_page_by_path('sample-page', OBJECT, 'page');

    if ($sample_page instanceof WP_Post) {
        $excluded_ids = isset($query_args['post__not_in']) ? (array) $query_args['post__not_in'] : array();
        $excluded_ids[] = $sample_page->ID;
        $query_args['post__not_in'] = array_values(array_unique(array_map('absint', $excluded_ids)));
    }

    return $query_args;
}
add_filter('wp_sitemaps_posts_query_args', 'atnif_exclude_sample_page_from_sitemap', 10, 2);

// テーマで使う基本機能を有効化
function atnif_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('style', 'script', 'navigation-widgets'));

    // header.php で全ページの正規URLを一元管理するため、標準の重複出力を止める。
    remove_action('wp_head', 'rel_canonical');
}
add_action('after_setup_theme', 'atnif_theme_setup');

// フロント側で使うCSS、Google Fonts、JavaScriptを読み込む
function atnif_enqueue_assets() {
    wp_enqueue_style('atnif-google-fonts', 'https://fonts.googleapis.com/css2?family=Zen+Old+Mincho:wght@400;500;600;700&display=swap', array(), null);
    wp_enqueue_style('atnif-style', get_stylesheet_uri(), array(), ATNIF_THEME_VERSION);


    if (is_page_template('page-sns-icon.php') || atnif_is_sns_icon_request()) {
        wp_enqueue_style('atnif-sns-icon', get_template_directory_uri() . '/assets/css/sns-icon.css', array('atnif-style'), ATNIF_THEME_VERSION);
    }

    if (atnif_is_blog_post_request()) {
        wp_enqueue_style(
            'atnif-blog-post',
            get_template_directory_uri() . '/assets/css/blog-post.css',
            array('atnif-style'),
            ATNIF_THEME_VERSION
        );
    }

    wp_enqueue_script('atnif-main', get_template_directory_uri() . '/assets/js/main.js', array(), ATNIF_THEME_VERSION, true);
}
add_action('wp_enqueue_scripts', 'atnif_enqueue_assets');

// Google Fontsの読み込みを早めるためのpreconnect情報を追加
function atnif_resource_hints($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }

    return $urls;
}
add_filter('wp_resource_hints', 'atnif_resource_hints', 10, 2);

// Google Fontsをpreloadして、フォントCSSの読み込みを非同期化する
function atnif_async_font_stylesheet($html, $handle, $href) {
    if ('atnif-google-fonts' !== $handle) {
        return $html;
    }

    $href = esc_url($href);

    return '<link rel="preload" as="style" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
        . '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>' . "\n";
}
add_filter('style_loader_tag', 'atnif_async_font_stylesheet', 10, 3);

// フロント画面では管理バーを非表示にする
function atnif_disable_frontend_admin_assets() {
    if (is_admin() || is_customize_preview()) {
        return;
    }

    add_filter('show_admin_bar', '__return_false');
}
add_action('after_setup_theme', 'atnif_disable_frontend_admin_assets');

// フロント画面で不要な管理バー関連アセットを読み込まないようにする
function atnif_dequeue_frontend_admin_assets() {
    if (is_admin() || is_customize_preview()) {
        return;
    }

    wp_dequeue_style('dashicons');
    wp_dequeue_style('admin-bar');
    wp_dequeue_script('admin-bar');
    wp_dequeue_style('googlesitekit-adminbar');
    wp_dequeue_style('googlesitekit-adminbar-css');
    wp_dequeue_script('googlesitekit-adminbar');
}
add_action('wp_enqueue_scripts', 'atnif_dequeue_frontend_admin_assets', 100);

// 独自ページのURLをWordPressのクエリに変換する
function atnif_add_rewrite_rules() {
    add_rewrite_rule('^sns-icon/?$', 'index.php?atnif_sns_icon=1', 'top');
    add_rewrite_rule('^blog/([^/]+)/?$', 'index.php?name=$matches[1]&post_type=post&atnif_blog_post=$matches[1]', 'top');
    add_rewrite_rule('^blog/?$', 'index.php?atnif_blog=1', 'top');
    add_rewrite_rule('^blog/page/([0-9]+)/?$', 'index.php?atnif_blog=1&paged=$matches[1]', 'top');
}
add_action('init', 'atnif_add_rewrite_rules');

// WordPressで受け取れる独自クエリ変数を追加する
function atnif_query_vars($vars) {
    $vars[] = 'atnif_sns_icon';
    $vars[] = 'atnif_blog';
    $vars[] = 'atnif_blog_post';

    return $vars;
}
add_filter('query_vars', 'atnif_query_vars');

// 現在のリクエストパスをサイトURLの階層を考慮して正規化する
function atnif_blog_request_path() {
    $request_path = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    $request_path = parse_url($request_path, PHP_URL_PATH);
    $request_path = $request_path ? '/' . trim($request_path, '/') . '/' : '/';

    $home_path = parse_url(home_url('/'), PHP_URL_PATH);
    $home_path = $home_path ? '/' . trim($home_path, '/') . '/' : '/';

    if ('/' !== $home_path && strpos($request_path, $home_path) === 0) {
        $request_path = '/' . ltrim(substr($request_path, strlen($home_path)), '/');
        $request_path = '/' === $request_path ? '/' : '/' . trim($request_path, '/') . '/';
    }

    return $request_path;
}

// 固定ページが未作成でも /sns-icon/ を配布ページとして扱う
function atnif_is_sns_icon_request() {
    return (bool) get_query_var('atnif_sns_icon')
        || '/sns-icon/' === atnif_blog_request_path();
}

// 現在の表示が独自ブログページへのリクエストか判定する
function atnif_is_blog_request() {
    $request_path = atnif_blog_request_path();

    return (bool) get_query_var('atnif_blog')
        || atnif_is_blog_post_request()
        || '/blog/' === $request_path
        || preg_match('#^/blog/(?:page/)?[0-9]+/$#', $request_path);
}

// 現在の表示が独自ブログ詳細ページへのリクエストか判定する
function atnif_is_blog_post_request() {
    return '' !== atnif_blog_post_path_segment();
}

// リクエストパスからブログ詳細部分の投稿IDまたはスラッグを取得する
function atnif_blog_post_path_segment() {
    $request_path = atnif_blog_request_path();

    if (!preg_match('#^/blog/([^/]+)/$#', $request_path, $matches)) {
        return '';
    }

    return sanitize_title(rawurldecode($matches[1]));
}

// リクエストされた投稿IDまたはスラッグからブログ記事の投稿IDを取得する
function atnif_blog_post_id() {
    $path_segment = atnif_blog_post_path_segment();

    if ('' === $path_segment) {
        return 0;
    }

    return atnif_resolve_blog_post_id($path_segment);
}

// パーマリンク設定が「基本」の環境でも、クエリ判定前にブログ詳細を単一投稿へ変換する
function atnif_route_blog_post_request($query_vars) {
    $path_segment = atnif_blog_post_path_segment();

    if ('' === $path_segment) {
        return $query_vars;
    }

    $query_vars['post_type'] = 'post';
    $query_vars['atnif_blog_post'] = $path_segment;

    $post_id = atnif_blog_post_id();

    if ($post_id) {
        $query_vars['p'] = $post_id;
        unset($query_vars['name']);
    } else {
        $query_vars['name'] = $path_segment;
        unset($query_vars['p']);
    }

    unset($query_vars['pagename'], $query_vars['page_id']);

    return $query_vars;
}
add_filter('request', 'atnif_route_blog_post_request', 1);

// リライトルールが未更新の環境でも同じURL解決処理を使用する
function atnif_route_blog_post_query($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    $path_segment = atnif_blog_post_path_segment();

    if ('' === $path_segment) {
        return;
    }

    $post_id = atnif_blog_post_id();

    if ($post_id) {
        $query->set('p', $post_id);
        $query->set('name', '');
    } else {
        $query->set('p', 0);
        $query->set('name', $path_segment);
    }

    $query->set('post_type', 'post');
    $query->set('posts_per_page', 1);
}
add_action('pre_get_posts', 'atnif_route_blog_post_query', 1);

// WordPressで設定された投稿スラッグからブログ詳細ページのURLを生成する
function atnif_blog_post_url($post_id) {
    $post = get_post($post_id);

    if (!$post instanceof WP_Post || 'post' !== $post->post_type) {
        return home_url('/blog/');
    }

    // 新規下書きはスラッグが空の場合があるため、プレビューでは投稿IDを一時的に使う。
    $path_segment = '' !== $post->post_name ? $post->post_name : (string) $post->ID;

    return home_url('/blog/' . $path_segment . '/');
}

// 旧スラッグと旧形式の投稿IDを、現在の公開URLへ直接転送する
function atnif_redirect_legacy_blog_post_url() {
    $path_segment = atnif_blog_post_path_segment();

    if ('' === $path_segment || is_preview()
        || !in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', array('GET', 'HEAD'), true)) {
        return;
    }

    $post_id = atnif_blog_post_id();
    $post = $post_id ? get_post($post_id) : null;

    if (!$post instanceof WP_Post || 'post' !== $post->post_type || 'publish' !== $post->post_status) {
        return;
    }

    $redirect_url = atnif_blog_post_url($post->ID);

    if ($post->post_name === $path_segment) {
        return;
    }

    wp_safe_redirect($redirect_url, 301, 'atnif-theme');
    exit;
}
add_action('template_redirect', 'atnif_redirect_legacy_blog_post_url', 1);

// WordPress標準の投稿URLへ再転送されないよう独自ブログURLを維持する
function atnif_disable_default_blog_post_canonical_redirect($redirect_url) {
    if (atnif_is_blog_post_request()) {
        return false;
    }

    return $redirect_url;
}
add_filter('redirect_canonical', 'atnif_disable_default_blog_post_canonical_redirect');

// 管理画面、REST API、テーマ内の投稿URLを独自ブログURLへ統一する
function atnif_filter_blog_post_permalink($permalink, $post, $leavename) {
    if (!$post instanceof WP_Post || 'post' !== $post->post_type) {
        return $permalink;
    }

    if ($leavename) {
        return home_url('/blog/%postname%/');
    }

    return atnif_blog_post_url($post->ID);
}
add_filter('post_link', 'atnif_filter_blog_post_permalink', 10, 3);

// 独自ブログページで表示すべき現在のページ番号を取得
function atnif_blog_current_page() {
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $request_path = atnif_blog_request_path();

    if (preg_match('#^/blog/page/([0-9]+)/$#', $request_path, $matches)) {
        $paged = max($paged, (int) $matches[1]);
    }

    return $paged;
}

// リライトルールのバージョンが変わったときだけルールを再生成
function atnif_flush_rewrite_rules_once() {
    if (get_option('atnif_rewrite_version') === ATNIF_REWRITE_VERSION) {
        return;
    }

    atnif_add_rewrite_rules();
    flush_rewrite_rules(true);
    update_option('atnif_rewrite_version', ATNIF_REWRITE_VERSION);
}
add_action('init', 'atnif_flush_rewrite_rules_once', 20);

// 独自URLでは対応する専用テンプレートを使用
function atnif_blog_template($template) {
    if (atnif_is_sns_icon_request()) {
        $sns_icon_template = get_template_directory() . '/page-sns-icon.php';

        if (file_exists($sns_icon_template)) {
            status_header(200);
            return $sns_icon_template;
        }
    }

    if (atnif_is_blog_post_request()) {
        $blog_post_template = get_template_directory() . '/single-blog.php';

        if (file_exists($blog_post_template)) {
            return $blog_post_template;
        }
    }

    if (atnif_is_blog_request()) {
        $blog_template = get_template_directory() . '/page-blog.php';

        if (file_exists($blog_template)) {
            return $blog_template;
        }
    }

    return $template;
}
add_filter('template_include', 'atnif_blog_template');

// ブログ記事の正規URLをWordPressで設定されたスラッグ形式へ統一する
function atnif_blog_post_canonical_url($canonical_url, $post) {
    if (atnif_is_blog_post_request() && $post instanceof WP_Post && 'post' === $post->post_type) {
        return atnif_blog_post_url($post->ID);
    }

    return $canonical_url;
}
add_filter('get_canonical_url', 'atnif_blog_post_canonical_url', 10, 2);

// 一覧の先頭投稿に影響されず、現在表示しているページの正規URLを返す
function atnif_canonical_url() {
    if (is_404() || is_search()) {
        return '';
    }

    if (atnif_is_sns_icon_request()) {
        return home_url('/sns-icon/');
    }

    if (atnif_is_blog_post_request()) {
        $post_id = atnif_blog_post_id();

        return $post_id ? atnif_blog_post_url($post_id) : '';
    }

    if (atnif_is_blog_request()) {
        $current_page = atnif_blog_current_page();

        return 1 < $current_page ? home_url('/blog/page/' . $current_page . '/') : home_url('/blog/');
    }

    if (is_front_page()) {
        return ATNIF_CANONICAL_ORIGIN . '/';
    }

    if (is_category()) {
        $category = get_queried_object();

        if ($category instanceof WP_Term) {
            $category_url = get_term_link($category);

            if (!is_wp_error($category_url)) {
                $paged = max(1, (int) get_query_var('paged'));
                return 1 < $paged ? get_pagenum_link($paged) : $category_url;
            }
        }
    }

    if (is_singular()) {
        $queried_object_id = get_queried_object_id();

        return $queried_object_id ? (string) wp_get_canonical_url($queried_object_id) : '';
    }

    return '';
}

// 独自ブログページのドキュメントタイトルを設定
function atnif_blog_document_title($title) {
    if (atnif_is_sns_icon_request()) {
        $title['title'] = __('SNSアイコン無料配布', 'atnif');
        $title['site'] = get_bloginfo('name');

        return $title;
    }

    if (atnif_is_blog_post_request()) {
        $post = get_post(atnif_blog_post_id());

        if ($post instanceof WP_Post && 'post' === $post->post_type) {
            $title['title'] = get_the_title($post);
            $title['site'] = get_bloginfo('name');
        }

        return $title;
    }

    if (atnif_is_blog_request() && !atnif_is_blog_post_request()) {
        $title['title'] = __('制作ブログ', 'atnif');
        $title['site'] = get_bloginfo('name');

        return $title;
    }

    if (is_front_page()) {
        $title['title'] = '恋愛ノベルゲーム『@Nif』公式サイト';
        unset($title['tagline']);
    }

    return $title;
}
add_filter('document_title_parts', 'atnif_blog_document_title');

// 検索結果とSNS共有で使用する、ページ固有の説明文を返す
function atnif_meta_description() {
    if (is_404()) {
        return '';
    }

    if (atnif_is_sns_icon_request()) {
        return '恋愛ノベルゲーム『@Nif』のキャラクター「なぎ」「岡都トキ」のSNSアイコンを無料配布しています。利用上の注意をご確認のうえご使用ください。';
    }

    if (atnif_is_blog_post_request()) {
        $post = get_post(atnif_blog_post_id());

        if ($post instanceof WP_Post) {
            $description = has_excerpt($post) ? $post->post_excerpt : $post->post_content;
            $description = strip_shortcodes($description);
            $description = preg_replace('/\s+/u', ' ', wp_strip_all_tags($description));

            return wp_html_excerpt(trim((string) $description), 160, '…');
        }
    }

    if (atnif_is_blog_request()) {
        return '恋愛ノベルゲーム『@Nif』の制作ブログ。ゲーム開発、イラスト、舞台となる大阪・高槻での取材、制作チームの日々を紹介します。';
    }

    if (is_front_page()) {
        return '大阪府高槻市を舞台に、失われた記憶と初恋を描く恋愛ノベルゲーム『@Nif』公式サイト。あらすじ、登場人物、制作ブログ、SNSアイコン、公開情報を紹介します。';
    }

    if (is_category()) {
        $description = wp_strip_all_tags(category_description());

        if ('' !== trim($description)) {
            return wp_html_excerpt(trim($description), 160, '…');
        }

        return sprintf('恋愛ノベルゲーム『@Nif』の「%s」に関する制作ブログ記事一覧です。', single_cat_title('', false));
    }

    return '';
}

// meta description、OGP、Xカードを一元的に出力する
function atnif_output_social_meta() {
    if (is_404()) {
        return;
    }

    $description = atnif_meta_description();
    $canonical_url = atnif_canonical_url();
    $title = wp_get_document_title();
    $image_url = atnif_asset_url('images/key-visual.png');
    $og_type = 'website';

    if (atnif_is_blog_post_request()) {
        $post_id = atnif_blog_post_id();
        $thumbnail_url = $post_id ? get_the_post_thumbnail_url($post_id, 'full') : '';
        $image_url = $thumbnail_url ?: $image_url;
        $og_type = 'article';
    } elseif (atnif_is_sns_icon_request()) {
        $image_url = atnif_asset_url('images/sns-banner.png');
    }

    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }

    echo '<meta property="og:locale" content="ja_JP">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
    echo '<meta property="og:site_name" content="@Nif">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";

    if ($description) {
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    }

    if ($canonical_url) {
        echo '<meta property="og:url" content="' . esc_url($canonical_url) . '">' . "\n";
    }

    if ($image_url) {
        echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
    } else {
        echo '<meta name="twitter:card" content="summary">' . "\n";
    }
}
add_action('wp_head', 'atnif_output_social_meta', 2);

// 検索エンジンがサイトとブログ記事を理解できるようJSON-LDを出力する
function atnif_output_structured_data() {
    $graph = array();

    if (is_front_page() && !atnif_is_blog_request() && !atnif_is_sns_icon_request()) {
        $same_as = array_filter(array(
            atnif_mod('x_url'),
            atnif_mod('tiktok_url'),
            atnif_mod('youtube_url'),
        ));

        $graph[] = array(
            '@type' => 'WebSite',
            '@id' => home_url('/#website'),
            'url' => home_url('/'),
            'name' => '@Nif',
            'alternateName' => 'あっとにふ',
            'inLanguage' => 'ja',
        );
        $graph[] = array(
            '@type' => 'Organization',
            '@id' => home_url('/#organization'),
            'url' => home_url('/'),
            'name' => '@Nif',
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => atnif_asset_url('images/icon.png'),
            ),
            'sameAs' => array_values($same_as),
        );
    }

    if (atnif_is_blog_post_request()) {
        $post = get_post(atnif_blog_post_id());

        if ($post instanceof WP_Post && 'publish' === $post->post_status) {
            $canonical_url = atnif_blog_post_url($post->ID);
            $image_url = get_the_post_thumbnail_url($post->ID, 'full') ?: atnif_asset_url('images/key-visual.png');
            $author_name = get_the_author_meta('display_name', (int) $post->post_author);

            $graph[] = array(
                '@type' => 'BlogPosting',
                '@id' => $canonical_url . '#article',
                'mainEntityOfPage' => array(
                    '@type' => 'WebPage',
                    '@id' => $canonical_url,
                ),
                'headline' => get_the_title($post),
                'description' => atnif_meta_description(),
                'image' => array($image_url),
                'datePublished' => get_the_date(DATE_W3C, $post),
                'dateModified' => get_the_modified_date(DATE_W3C, $post),
                'inLanguage' => 'ja',
                'author' => array(
                    '@type' => 'Person',
                    'name' => $author_name ?: '@Nif制作チーム',
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    '@id' => home_url('/#organization'),
                    'name' => '@Nif',
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => atnif_asset_url('images/icon.png'),
                    ),
                ),
            );
            $graph[] = array(
                '@type' => 'BreadcrumbList',
                '@id' => $canonical_url . '#breadcrumb',
                'itemListElement' => array(
                    array(
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'トップ',
                        'item' => home_url('/'),
                    ),
                    array(
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => '制作ブログ',
                        'item' => home_url('/blog/'),
                    ),
                    array(
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => get_the_title($post),
                        'item' => $canonical_url,
                    ),
                ),
            );
        }
    }

    if (!$graph) {
        return;
    }

    $data = array(
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    );

    echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action('wp_head', 'atnif_output_structured_data', 3);

// 内容のない補助アーカイブはインデックスさせず、カテゴリだけを検索対象にする
function atnif_filter_robots($robots) {
    if (is_author() || is_date() || is_tag() || is_search() || is_attachment() || is_404()) {
        $robots['noindex'] = true;
    }

    return $robots;
}
add_filter('wp_robots', 'atnif_filter_robots');

// noindexにする著者サイトマップを標準サイトマップから除外する
function atnif_filter_sitemap_provider($provider, $name) {
    if ('users' === $name) {
        return false;
    }

    return $provider;
}
add_filter('wp_sitemaps_add_provider', 'atnif_filter_sitemap_provider', 10, 2);

// noindexにするタグをサイトマップから除外する
function atnif_filter_sitemap_taxonomies($taxonomies) {
    unset($taxonomies['post_tag']);

    return $taxonomies;
}
add_filter('wp_sitemaps_taxonomies', 'atnif_filter_sitemap_taxonomies');

// WordPress上に固定ページを持たない独自公開URLをサイトマップへ追加する
class ATNIF_Sitemaps_Public_Pages extends WP_Sitemaps_Provider {
    public function __construct() {
        $this->name = 'public-pages';
        $this->object_type = 'atnif';
    }

    public function get_url_list($page_num, $object_subtype = '') {
        if (1 !== (int) $page_num) {
            return array();
        }

        return array(
            array('loc' => home_url('/blog/')),
            array('loc' => home_url('/sns-icon/')),
        );
    }

    public function get_max_num_pages($object_subtype = '') {
        return 1;
    }
}

function atnif_register_sitemap_provider() {
    wp_register_sitemap_provider('public-pages', new ATNIF_Sitemaps_Public_Pages());
}
add_action('init', 'atnif_register_sitemap_provider', 20);

// assets配下のファイルURLを最適化済みパスに変換して返す
function atnif_asset_url($path) {
    $path = ltrim($path, '/');
    $asset_url = get_template_directory_uri() . '/assets/' . atnif_optimized_asset_path($path);

    // 長期キャッシュ中でもテーマ更新時に最適化済み画像へ確実に切り替える。
    return add_query_arg('ver', ATNIF_THEME_VERSION, $asset_url);
}

// assets配下に指定ファイルが存在するか確認
function atnif_asset_file_exists($path) {
    return file_exists(get_template_directory() . '/assets/' . ltrim($path, '/'));
}

// PNG画像などをWebPや軽量版があればそちらのパスへ差し替え
function atnif_optimized_asset_path($path) {
    $path = ltrim($path, '/');
    $optimized = array(
        'images/special-mascot.png' => 'images/special-mascot-404.webp',
        'images/game-badge.png' => 'images/game-badge-400.webp',
    );

    if (isset($optimized[$path]) && atnif_asset_file_exists($optimized[$path])) {
        return $optimized[$path];
    }

    if (preg_match('/\.png$/', $path)) {
        $webp_path = preg_replace('/\.png$/', '.webp', $path);

        if ($webp_path && atnif_asset_file_exists($webp_path)) {
            return $webp_path;
        }
    }

    return $path;
}

// テーマassets内の画像URLなら最適化済み画像URLに差し替え
function atnif_maybe_optimized_image_url($url) {
    $assets_url = get_template_directory_uri() . '/assets/';

    if (strpos($url, $assets_url) !== 0) {
        return $url;
    }

    $relative_path = (string) parse_url(substr($url, strlen($assets_url)), PHP_URL_PATH);

    return atnif_asset_url($relative_path);
}

// ファーストビューで使う画像を優先読み込み
function atnif_preload_hero_background() {
    if (!is_front_page() || atnif_is_blog_request() || atnif_is_sns_icon_request()) {
        return;
    }

    $hero_url = atnif_asset_url('images/key-visual.png');
    $mobile_nagi_hero_url = atnif_asset_url('images/mb-key-visual__nagi.png');

    echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '" media="(min-width: 761px)" fetchpriority="high">' . "\n";
    echo '<link rel="preload" as="image" href="' . esc_url($mobile_nagi_hero_url) . '" media="(max-width: 760px)" fetchpriority="high">' . "\n";
}
add_action('wp_head', 'atnif_preload_hero_background', 1);

// カスタマイザー設定が未入力のときに使う初期値
function atnif_default($key) {
    $defaults = array(
        'story_text' => "転勤族の親に連れられ、日本中を転々としてきた高校生・佐々木巡は、\n幼い頃を過ごした大阪府高槻市へ久しぶりに戻ってくる。引っ越し準備の最中、\n古い段ボールの底から見つけたのは、折れ目だらけの小さなメモ。\nそこには、「またここで会おう」とだけ書かれていた。\n誰が書いたのかも、いつ受け取ったのかも思い出せない。\nそれなのに、その言葉は巡の胸の奥で、ずっと消えずに残っていた約束のように疼きはじめる。\n\n“ここ” とはどこなのか。自分は誰と、何を約束したのか。\n答えのない問いに導かれるように、巡は夏の陽炎に揺れる高槻の街を歩き出す。\nそこで出会うのは、正体も探しものも曖昧なまま旅を続ける少女・なぎ。\n由緒ある神社で、過ぎ去るはずの一日を何度も写し続ける写真部の先輩岡都トキ。\nそして、存在しないはずの映画館で、自分の物語を観てくれる誰かを待ち続けていた少女。\n\n彼女たちと過ごす時間は、巡の失われた記憶を少しずつ照らしていく。\n懐かしい道、夕立の匂い、蝉時雨の境内、スクリーンに映る誰かの恋。\n忘れていたはずの景色の中で、巡は何度も出会い、別れ、そして気づいていく。\n探していたのは、ただの場所ではなかったのだと。\n\n夏の終わり、巡はメモに残された “ここ” の本当の意味へ辿り着く。\nそれは、かつて誰かと交わした小さな約束であり、\n忘れてしまっても消えることのなかった、初恋の残響だった。",
        'scenario_title1' => '√ - なぎ',
        'scenario_text1' => "夏休み、巡は「またここで会おう」と書かれたメモに導かれるように大阪の街を歩き、道に迷う少女・なぎと出会う。彼女は自分の正体も曖昧なまま、名も知らぬ「なくしモノ」を探していた。\n\n記憶にない場所 “ここ” を探す巡は、彼女の孤独に自分と似たものを感じ、ともに街を巡ることにする。夏空の下、雑踏や路地、神社を歩くうち、巡には幼い日の記憶が、ナギには忘れていた過去が少しずつ蘇っていく。\n\n巡はやがて知る。彼女の「なくしモノ」を見つけることは、ナギとの時間の終わりを意味するのだと。",
        'scenario_title2' => '√ - トキ',
        'scenario_text2' => "欠落した記憶の手がかりを求め、巡は写真部の先輩・岡都トキに誘われるまま、彼女の実家である神社で古い奉納写真の整理を手伝うことになる。\n\n蝉時雨に包まれた夏休みの境内。埃っぽい社務所で冷えた麦茶を飲みながら、膨大な写真と向き合う時間は、不思議と穏やかで懐かしかった。トキとの他愛ない会話、近所から届くスイカ、差し込む夕暮れの光。単調な日々の中で、巡はいつか失われた記憶の「一枚」に辿り着けると信じていた。\n\nある朝、巡は気づく。昨日と同じ蝉の声、同じ参拝客、同じ言葉。神社の夏は、同じ一日を繰り返していた。",
        'character_image1' => atnif_asset_url('images/characterImage_nagi.png'),
        'character_select_icon1' => atnif_asset_url('images/selectIcon-nagi.png'),
        'character_name1' => 'なぎ',
        'character_ruby1' => 'nagi',
        'character_copy1' => "どこか浮世めいた旅行客\n”なくしモノ”を探している",
        'character_image2' => atnif_asset_url('images/characterImage_toki.png'),
        'character_select_icon2' => atnif_asset_url('images/selectIcon-toki.png'),
        'character_name2' => '岡都 トキ',
        'character_ruby2' => 'okato toki',
        'character_copy2' => "巡と一緒の写真部に所属している女の子\n”由緒正しき社家の一人娘",
        'character_image3' => atnif_asset_url('images/characterImage_meguru.png'),
        'character_select_icon3' => atnif_asset_url('images/selectIcon-meguru.png'),
        'character_name3' => '佐々木 巡',
        'character_ruby3' => 'sasaki meguru',
        'character_copy3' => "大阪府高槻市の高校生",
        'gallery_1' => atnif_asset_url('images/comingsoon.png'),
        'gallery_2' => atnif_asset_url('images/comingsoon.png'),
        'gallery_3' => atnif_asset_url('images/comingsoon.png'),
        'gallery_4' => atnif_asset_url('images/comingsoon.png'),
        'gallery_5' => atnif_asset_url('images/comingsoon.png'),
        'gallery_6' => atnif_asset_url('images/comingsoon.png'),
        'special_title' => '@Nif 公式SNS',
        'special_copy' => "コンテンツ投稿中です\nFollow me ~",
        'x_url' => 'https://x.com/_atnif',
        'tiktok_url' => 'https://www.tiktok.com/@nif06010',
        'youtube_url' => 'https://www.youtube.com/@%E3%81%82%E3%81%A3%E3%81%A8%E3%81%AB%E3%81%B5',
        'special_image' => atnif_asset_url('images/special-mascot.png'),
        'game_title' => '@Nif',
        'game_status' => '鋭意制作中',
        'game_link_label' => 'ノベルゲームコレクションにて公開予定',
        'game_link_url' => '',
        'game_meta' => "ジャンル　恋愛ノベルゲーム\n対応言語　日本語",
        'game_badge' => atnif_asset_url('images/game-badge.png'),
        'footer_text' => '© 2026 @Nif All Rights Reserved.',
    );

    return isset($defaults[$key]) ? $defaults[$key] : '';
}

// カスタマイザーの値を、未設定時は初期値付きで取得
function atnif_mod($key) {
    return get_theme_mod('atnif_' . $key, atnif_default($key));
}

// カスタマイザー画像設定からimgタグのHTMLを生成
function atnif_image_mod($key, $class = '', $alt = '', $size = 'full', $attrs = array()) {
    $value = atnif_mod($key);

    $attrs = wp_parse_args($attrs, array(
        'class' => $class,
        'alt' => $alt,
        'decoding' => 'async',
        'loading' => 'lazy',
    ));

    if (is_numeric($value)) {
        return wp_get_attachment_image((int) $value, $size, false, $attrs);
    }

    if ($value) {
        $attrs['src'] = atnif_maybe_optimized_image_url($value);

        $html_attrs = '';
        foreach ($attrs as $name => $attr_value) {
            if ('' === $attr_value || null === $attr_value || false === $attr_value) {
                continue;
            }

            $escaped_value = 'src' === $name ? esc_url($attr_value) : esc_attr($attr_value);
            $html_attrs .= ' ' . esc_attr($name) . '="' . $escaped_value . '"';
        }

        return '<img' . $html_attrs . '>';
    }

    return '';
}

// カスタマイザー画像設定から画像URLだけを取得
function atnif_image_url_mod($key) {
    $value = atnif_mod($key);

    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'full') ?: '';
    }

    return atnif_maybe_optimized_image_url($value);
}

// テキストエリアの内容をHTMLエスケープして出力用に整える
function atnif_textarea($text) {
    return esc_html($text);
}

// URL入力を空文字許可つきでサニタイズ
function atnif_sanitize_url_or_empty($value) {
    $value = trim((string) $value);
    return $value === '' ? '' : esc_url_raw($value);
}

// ストーリー本文で許可されたHTMLだけを残してサニタイズ
function atnif_sanitize_story_html($value) {
    return wp_kses_post($value);
}

// WordPressカスタマイザーに各セクションの編集項目の登録
function atnif_customize_register($wp_customize) {
    $wp_customize->add_panel('atnif_content', array(
        'title' => __('@Nif Page Content', 'atnif'),
        'priority' => 30,
    ));

    $sections = array(
        'story' => __('Story', 'atnif'),
        'character' => __('Character', 'atnif'),
        'gallery' => __('Gallery', 'atnif'),
        'special' => __('Special', 'atnif'),
        'game' => __('Game', 'atnif'),
        'footer' => __('Footer', 'atnif'),
    );

    foreach ($sections as $id => $title) {
        $wp_customize->add_section('atnif_' . $id, array(
            'title' => $title,
            'panel' => 'atnif_content',
        ));
    }

    $text_fields = array(
        'story' => array(
            'story_text' => array(__('Story text', 'atnif'), 'textarea'),
            'scenario_title1' => array(__('Scenario title', 'atnif'), 'text'),
            'scenario_text1' => array(__('Scenario text', 'atnif'), 'textarea'),
            'scenario_title2' => array(__('Scenario title2', 'atnif'), 'text'),
            'scenario_text2' => array(__('Scenario text2', 'atnif'), 'textarea'),
        ),
        'character' => array(
            'character_name1' => array(__('Character name', 'atnif'), 'text'),
            'character_ruby1' => array(__('Character romanization', 'atnif'), 'text'),
            'character_copy1' => array(__('Character description', 'atnif'), 'textarea'),
            'character_name2' => array(__('Character name2', 'atnif'), 'text'),
            'character_ruby2' => array(__('Character romanization2', 'atnif'), 'text'),
            'character_copy2' => array(__('Character description2', 'atnif'), 'textarea'),
            'character_name3' => array(__('Character name3', 'atnif'), 'text'),
            'character_ruby3' => array(__('Character romanization3', 'atnif'), 'text'),
            'character_copy3' => array(__('Character description3', 'atnif'), 'textarea'),
        ),
        'special' => array(
            'special_title' => array(__('SNS title', 'atnif'), 'text'),
            'special_copy' => array(__('SNS copy', 'atnif'), 'textarea'),
            'x_url' => array(__('X URL', 'atnif'), 'url'),
            'tiktok_url' => array(__('TikTok URL', 'atnif'), 'url'),
            'youtube_url' => array(__('YouTube URL', 'atnif'), 'url'),
        ),
        'game' => array(
            'game_title' => array(__('Game title', 'atnif'), 'text'),
            'game_status' => array(__('Game status text', 'atnif'), 'text'),
            'game_link_label' => array(__('Game link label', 'atnif'), 'text'),
            'game_link_url' => array(__('Game link URL', 'atnif'), 'url'),
            'game_meta' => array(__('Game details', 'atnif'), 'textarea'),
        ),
        'footer' => array(
            'footer_text' => array(__('Copyright text', 'atnif'), 'text'),
        ),
    );

    foreach ($text_fields as $section => $fields) {
        foreach ($fields as $key => $field) {
            $type = $field[1];
            $sanitize_callback = $type === 'url' ? 'atnif_sanitize_url_or_empty' : 'sanitize_textarea_field';

            if ($section === 'story') {
                $sanitize_callback = 'atnif_sanitize_story_html';
            }

            $wp_customize->add_setting('atnif_' . $key, array(
                'default' => atnif_default($key),
                'sanitize_callback' => $sanitize_callback,
            ));
            $wp_customize->add_control('atnif_' . $key, array(
                'label' => $field[0],
                'section' => 'atnif_' . $section,
                'type' => $type,
            ));
        }
    }

    $image_fields = array(
        'character' => array(
            'character_image1' => __('Character image 1', 'atnif'),
            'character_select_icon1' => __('Character select icon 1', 'atnif'),
            'character_image2' => __('Character image 2', 'atnif'),
            'character_select_icon2' => __('Character select icon 2', 'atnif'),
            'character_select_icon3' => __('Character select icon 3', 'atnif'),
        ),
        'gallery' => array(
            'gallery_1' => __('Gallery image 1', 'atnif'),
            'gallery_2' => __('Gallery image 2', 'atnif'),
            'gallery_3' => __('Gallery image 3', 'atnif'),
            'gallery_4' => __('Gallery image 4', 'atnif'),
            'gallery_5' => __('Gallery image 5', 'atnif'),
            'gallery_6' => __('Gallery image 6', 'atnif'),
        ),
        'special' => array(
            'special_image' => __('Special illustration', 'atnif'),
        ),
        'game' => array(
            'game_badge' => __('Game badge image', 'atnif'),
        ),
    );

    foreach ($image_fields as $section => $fields) {
        foreach ($fields as $key => $label) {
            $wp_customize->add_setting('atnif_' . $key, array(
                'default' => atnif_default($key),
                'sanitize_callback' => 'esc_url_raw',
            ));
            $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'atnif_' . $key, array(
                'label' => $label,
                'section' => 'atnif_' . $section,
                'settings' => 'atnif_' . $key,
            )));
        }
    }
}
add_action('customize_register', 'atnif_customize_register');

// ヘッダーナビゲーションに表示する項目一覧を返す
function atnif_nav_items() {
    return array(
        'story' => array('Story', 'あらすじ'),
        'character' => array('Character', '登場人物'),
        'gallery' => array('Gallery', '写真展示'),
        'special' => array('Special', 'おたのしみ'),
        'blog' => array('Blog', 'ブログ'),
        'game' => array('Game', 'ゲーム'),
    );
}

function atnif_nav_url($anchor) {
    $anchor = sanitize_key($anchor);

    if ('blog' === $anchor) {
        return home_url('/blog/');
    }

    // 独自ページはトップページ判定が残るため、ページ内リンクの対象から除外する。
    if (!atnif_is_blog_request() && !atnif_is_sns_icon_request() && (is_front_page() || is_home())) {
        return '#' . $anchor;
    }

    return home_url('/#' . $anchor);
}
