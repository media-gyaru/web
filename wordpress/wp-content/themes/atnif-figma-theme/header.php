<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="SPMgwl0H6buxpFyCnFkpV2fi2s45zgu_gpFD1Em8GLU" />
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
<div class="site-shell">
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
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
