<?php
/**
 * ブログ一覧とアーカイブで共有する記事カード。
 *
 * @package atnif
 */

if (!defined('ABSPATH')) {
    exit;
}

$categories = get_the_category();
?>

<article <?php post_class('blog-card'); ?>>
    <?php if ($categories) : ?>
        <p class="blog-card__categories">
            <?php foreach ($categories as $index => $category) : ?>
                <?php if (0 < $index) : ?>, <?php endif; ?>
                <a href="<?php echo esc_url(get_category_link($category)); ?>"><?php echo esc_html($category->name); ?></a>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

    <a class="blog-card__link" href="<?php echo esc_url(atnif_blog_post_url(get_the_ID())); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <figure class="blog-card__media">
                <?php the_post_thumbnail('medium_large', array('class' => 'blog-card__image')); ?>
            </figure>
        <?php else : ?>
            <div class="blog-card__media" aria-hidden="true"></div>
        <?php endif; ?>

        <div class="blog-card__body">
            <time class="blog-card__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                <?php echo esc_html(get_the_date('Y-m-d')); ?>
            </time>
            <h2 class="blog-card__title"><?php the_title(); ?></h2>
            <p class="blog-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 42)); ?></p>
        </div>
    </a>
</article>
