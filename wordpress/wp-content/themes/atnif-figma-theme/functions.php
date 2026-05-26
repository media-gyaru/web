<?php

if (!defined('ABSPATH')) {
    exit;
}

define('ATNIF_THEME_VERSION', '1.0.0');

function atnif_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height' => 71,
        'width' => 213,
        'flex-height' => true,
        'flex-width' => true,
    ));
    add_theme_support('html5', array('style', 'script', 'navigation-widgets'));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'atnif-figma'),
    ));
}
add_action('after_setup_theme', 'atnif_theme_setup');

function atnif_enqueue_assets() {
    wp_enqueue_style('atnif-google-fonts', 'https://fonts.googleapis.com/css2?family=Zen+Old+Mincho:wght@400;500&display=swap', array(), null);
    wp_enqueue_style('atnif-style', get_stylesheet_uri(), array('atnif-google-fonts'), ATNIF_THEME_VERSION);
    wp_enqueue_script('atnif-main', get_template_directory_uri() . '/assets/js/main.js', array(), ATNIF_THEME_VERSION, true);
}
add_action('wp_enqueue_scripts', 'atnif_enqueue_assets');

function atnif_asset_url($path) {
    return get_template_directory_uri() . '/assets/' . ltrim($path, '/');
}

function atnif_default($key) {
    $defaults = array(
        'hero_image' => '',
        'hero_background' => atnif_asset_url('images/header-bg.png'),
        'story_text' => "転勤族の親に連れられ、日本中を転々としてきた高校生・佐々木巡は、\n幼い頃を過ごした大阪府高槻市へ久しぶりに戻ってくる。引っ越し準備の最中、\n古い段ボールの底から見つけたのは、折れ目だらけの小さなメモ。\nそこには、「またここで会おう」とだけ書かれていた。\n誰が書いたのかも、いつ受け取ったのかも思い出せない。\nそれなのに、その言葉は巡の胸の奥で、ずっと消えずに残っていた約束のように疼きはじめる。\n\n“ここ” とはどこなのか。自分は誰と、何を約束したのか。\n答えのない問いに導かれるように、巡は夏の陽炎に揺れる高槻の街を歩き出す。\nそこで出会うのは、正体も探しものも曖昧なまま旅を続ける少女・なぎ。\n由緒ある神社で、過ぎ去るはずの一日を何度も写し続ける写真部の先輩岡都トキ。\nそして、存在しないはずの映画館で、自分の物語を観てくれる誰かを待ち続けていた少女。\n\n彼女たちと過ごす時間は、巡の失われた記憶を少しずつ照らしていく。\n懐かしい道、夕立の匂い、蝉時雨の境内、スクリーンに映る誰かの恋。\n忘れていたはずの景色の中で、巡は何度も出会い、別れ、そして気づいていく。\n探していたのは、ただの場所ではなかったのだと。\n\n夏の終わり、巡はメモに残された “ここ” の本当の意味へ辿り着く。\nそれは、かつて誰かと交わした小さな約束であり、\n忘れてしまっても消えることのなかった、初恋の残響だった。",
        'scenario_title1' => '√ - なぎ',
        'scenario_text1' => "夏休み、巡は「またここで会おう」と書かれたメモに導かれるように大阪の街を歩き、道に迷う少女・なぎと出会う。彼女は自分の正体も曖昧なまま、名も知らぬ「なくしモノ」を探していた。\n\n記憶にない場所 “ここ” を探す巡は、彼女の孤独に自分と似たものを感じ、ともに街を巡ることにする。夏空の下、雑踏や路地、神社を歩くうち、巡には幼い日の記憶が、ナギには忘れていた過去が少しずつ蘇っていく。\n\n巡はやがて知る。彼女の「なくしモノ」を見つけることは、ナギとの時間の終わりを意味するのだと。",
        'scenario_title2' => '√ - トキ',
        'scenario_text2' => "欠落した記憶の手がかりを求め、巡は写真部の先輩・岡都トキに誘われるまま、彼女の実家である神社で古い奉納写真の整理を手伝うことになる。\n\n蝉時雨に包まれた夏休みの境内。埃っぽい社務所で冷えた麦茶を飲みながら、膨大な写真と向き合う時間は、不思議と穏やかで懐かしかった。トキとの他愛ない会話、近所から届くスイカ、差し込む夕暮れの光。単調な日々の中で、巡はいつか失われた記憶の「一枚」に辿り着けると信じていた。\n\nある朝、巡は気づく。昨日と同じ蝉の声、同じ参拝客、同じ言葉。神社の夏は、同じ一日を繰り返していた。",
        'scenario_title3' => 'シナリオタイトル3',
        'scenario_text3' => "あらすじテキスト（300文字程度）\n・\n・\n・\n・\n・\n・",
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
        'special_banner_1_label' => 'Coming Soon',
        'special_banner_1_url' => '',
        'special_banner_2_label' => 'Coming Soon',
        'special_banner_2_url' => '',
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

function atnif_mod($key) {
    return get_theme_mod('atnif_' . $key, atnif_default($key));
}

function atnif_image_mod($key, $class = '', $alt = '') {
    $value = atnif_mod($key);

    if (is_numeric($value)) {
        return wp_get_attachment_image((int) $value, 'full', false, array(
            'class' => $class,
            'alt' => $alt,
        ));
    }

    if ($value) {
        return '<img class="' . esc_attr($class) . '" src="' . esc_url($value) . '" alt="' . esc_attr($alt) . '">';
    }

    return '';
}

function atnif_image_url_mod($key) {
    $value = atnif_mod($key);

    if (is_numeric($value)) {
        return wp_get_attachment_image_url((int) $value, 'full') ?: '';
    }

    return $value;
}

function atnif_textarea($text) {
    return esc_html($text);
}

function atnif_sanitize_url_or_empty($value) {
    $value = trim((string) $value);
    return $value === '' ? '' : esc_url_raw($value);
}

function atnif_customize_register($wp_customize) {
    $wp_customize->add_panel('atnif_content', array(
        'title' => __('@Nif Page Content', 'atnif-figma'),
        'priority' => 30,
    ));

    $sections = array(
        'hero' => __('Main Visual', 'atnif-figma'),
        'story' => __('Story', 'atnif-figma'),
        'character' => __('Character', 'atnif-figma'),
        'gallery' => __('Gallery', 'atnif-figma'),
        'special' => __('Special', 'atnif-figma'),
        'game' => __('Game', 'atnif-figma'),
        'footer' => __('Footer', 'atnif-figma'),
    );

    foreach ($sections as $id => $title) {
        $wp_customize->add_section('atnif_' . $id, array(
            'title' => $title,
            'panel' => 'atnif_content',
        ));
    }

    $text_fields = array(
        'story' => array(
            'story_text' => array(__('Story text', 'atnif-figma'), 'textarea'),
            'scenario_title1' => array(__('Scenario title', 'atnif-figma'), 'text'),
            'scenario_text1' => array(__('Scenario text', 'atnif-figma'), 'textarea'),
            'scenario_title2' => array(__('Scenario title2', 'atnif-figma'), 'text'),
            'scenario_text2' => array(__('Scenario text2', 'atnif-figma'), 'textarea'),
            'scenario_title3' => array(__('Scenario title3', 'atnif-figma'), 'text'),
            'scenario_text3' => array(__('Scenario text3', 'atnif-figma'), 'textarea'),
        ),
        'character' => array(
            'character_name1' => array(__('Character name', 'atnif-figma'), 'text'),
            'character_ruby1' => array(__('Character romanization', 'atnif-figma'), 'text'),
            'character_copy1' => array(__('Character description', 'atnif-figma'), 'textarea'),
            'character_name2' => array(__('Character name2', 'atnif-figma'), 'text'),
            'character_ruby2' => array(__('Character romanization2', 'atnif-figma'), 'text'),
            'character_copy2' => array(__('Character description2', 'atnif-figma'), 'textarea'),
            'character_name3' => array(__('Character name3', 'atnif-figma'), 'text'),
            'character_ruby3' => array(__('Character romanization3', 'atnif-figma'), 'text'),
            'character_copy3' => array(__('Character description3', 'atnif-figma'), 'textarea'),
        ),
        'special' => array(
            'special_banner_1_label' => array(__('First banner label', 'atnif-figma'), 'text'),
            'special_banner_1_url' => array(__('First banner URL', 'atnif-figma'), 'url'),
            'special_banner_2_label' => array(__('Second banner label', 'atnif-figma'), 'text'),
            'special_banner_2_url' => array(__('Second banner URL', 'atnif-figma'), 'url'),
            'special_title' => array(__('SNS title', 'atnif-figma'), 'text'),
            'special_copy' => array(__('SNS copy', 'atnif-figma'), 'textarea'),
            'x_url' => array(__('X URL', 'atnif-figma'), 'url'),
            'tiktok_url' => array(__('TikTok URL', 'atnif-figma'), 'url'),
            'youtube_url' => array(__('YouTube URL', 'atnif-figma'), 'url'),
        ),
        'game' => array(
            'game_title' => array(__('Game title', 'atnif-figma'), 'text'),
            'game_status' => array(__('Game status text', 'atnif-figma'), 'text'),
            'game_link_label' => array(__('Game link label', 'atnif-figma'), 'text'),
            'game_link_url' => array(__('Game link URL', 'atnif-figma'), 'url'),
            'game_meta' => array(__('Game details', 'atnif-figma'), 'textarea'),
        ),
        'footer' => array(
            'footer_text' => array(__('Copyright text', 'atnif-figma'), 'text'),
        ),
    );

    foreach ($text_fields as $section => $fields) {
        foreach ($fields as $key => $field) {
            $type = $field[1];
            $wp_customize->add_setting('atnif_' . $key, array(
                'default' => atnif_default($key),
                'sanitize_callback' => $type === 'url' ? 'atnif_sanitize_url_or_empty' : 'sanitize_textarea_field',
            ));
            $wp_customize->add_control('atnif_' . $key, array(
                'label' => $field[0],
                'section' => 'atnif_' . $section,
                'type' => $type,
            ));
        }
    }

    $image_fields = array(
        'hero' => array(
            'hero_background' => __('Header and hero background image', 'atnif-figma'),
            'hero_image' => __('Main visual image', 'atnif-figma'),
        ),
        'character' => array(
            'character_image1' => __('Character image 1', 'atnif-figma'),
            'character_select_icon1' => __('Character select icon 1', 'atnif-figma'),
            'character_image2' => __('Character image 2', 'atnif-figma'),
            'character_select_icon2' => __('Character select icon 2', 'atnif-figma'),
            'character_image3' => __('Character image 3', 'atnif-figma'),
            'character_select_icon3' => __('Character select icon 3', 'atnif-figma'),
        ),
        'gallery' => array(
            'gallery_1' => __('Gallery image 1', 'atnif-figma'),
            'gallery_2' => __('Gallery image 2', 'atnif-figma'),
            'gallery_3' => __('Gallery image 3', 'atnif-figma'),
            'gallery_4' => __('Gallery image 4', 'atnif-figma'),
            'gallery_5' => __('Gallery image 5', 'atnif-figma'),
            'gallery_6' => __('Gallery image 6', 'atnif-figma'),
        ),
        'special' => array(
            'special_image' => __('Special illustration', 'atnif-figma'),
        ),
        'game' => array(
            'game_badge' => __('Game badge image', 'atnif-figma'),
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

function atnif_nav_items() {
    return array(
        'story' => array('Story', 'あらすじ'),
        'character' => array('Character', '登場人物'),
        'gallery' => array('Gallery', '写真展示'),
        'special' => array('Special', 'おたのしみ'),
        'game' => array('Game', 'ゲーム'),
    );
}
