<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="SPMgwl0H6buxpFyCnFkpV2fi2s45zgu_gpFD1Em8GLU" />
    <?php if (function_exists('wp_get_canonical_url') && wp_get_canonical_url()) : ?>
        <link rel="canonical" href="<?php echo esc_url(wp_get_canonical_url()); ?>" />
    <?php endif; ?>
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "wx0dqymwqh");
    </script>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<button class="fixed-nav-btn" type="button" aria-controls="mobile-nav" aria-expanded="false" data-mobile-nav-open>
    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nav-btn.svg'); ?>" alt="">
    <span class="screen-reader-text"><?php esc_html_e('Open navigation', 'atnif'); ?></span>
</button>
<nav id="mobile-nav" class="mobile-nav-panel" aria-label="<?php esc_attr_e('Mobile navigation', 'atnif'); ?>" aria-hidden="true" data-mobile-nav>
    <p class="mobile-nav-panel__label">MENU</p>
    <ul class="mobile-nav-panel__list">
        <?php foreach (atnif_nav_items() as $anchor => $labels) : ?>
            <li>
                <a class="mobile-nav-panel__link" href="<?php echo esc_url(atnif_nav_url($anchor)); ?>" data-mobile-nav-link>
                    <span class="mobile-nav-panel__en"><?php echo esc_html($labels[0]); ?></span>
                    <span class="mobile-nav-panel__ja"><?php echo esc_html($labels[1]); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <button class="mobile-nav-panel__close" type="button" aria-label="<?php esc_attr_e('Close navigation', 'atnif'); ?>" data-mobile-nav-close></button>
</nav>
<div class="site-shell">
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
            </a>
            <nav class="site-nav" aria-label="<?php esc_attr_e('Primary navigation', 'atnif'); ?>">
                <ul>
                    <?php foreach (atnif_nav_items() as $anchor => $labels) : ?>
                        <li>
                            <a href="<?php echo esc_url(atnif_nav_url($anchor)); ?>">
                                <span class="site-nav__en"><?php echo esc_html($labels[0]); ?></span>
                                <span class="site-nav__ja"><?php echo esc_html($labels[1]); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
