<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site-shell">
    <header class="site-header" style="background-image: url('<?php echo esc_url(atnif_mod('hero_background')); ?>');">
        <div class="site-header__inner">
            <a class="site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <?php
                if (has_custom_logo()) {
                    echo wp_get_attachment_image((int) get_theme_mod('custom_logo'), 'full', false, array(
                        'alt' => get_bloginfo('name'),
                    ));
                } else {
                    echo esc_html__('ロゴ', 'atnif-figma');
                }
                ?>
            </a>
            <nav class="site-nav" aria-label="<?php esc_attr_e('Primary navigation', 'atnif-figma'); ?>">
                <ul>
                    <?php foreach (atnif_nav_items() as $anchor => $labels) : ?>
                        <li>
                            <a href="#<?php echo esc_attr($anchor); ?>">
                                <span class="site-nav__en"><?php echo esc_html($labels[0]); ?></span>
                                <span class="site-nav__ja"><?php echo esc_html($labels[1]); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
