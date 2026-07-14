<?php
/**
 * 独自URL /blog/{id}/ で表示するブログ記事詳細テンプレート。
 *
 * @package atnif
 */

get_header();
?>

<main id="main" class="site-main blog-post-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article <?php post_class('blog-post'); ?>>
                <header class="blog-post__header">
                    <a class="blog-post__back" href="<?php echo esc_url(home_url('/blog/')); ?>">
                        <span aria-hidden="true">←
                            <?php esc_html_e('ブログ一覧へ', 'atnif'); ?>
                        </span>
                    </a>

                    <h1 class="blog-post__title"><?php the_title(); ?></h1>

                    <div class="blog-post__meta">
                        <time class="blog-post__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                            <?php echo esc_html(get_the_date('Y-m-d')); ?>
                        </time>

                        <?php $categories = get_the_category(); ?>
                        <?php if ($categories) : ?>
                            <span class="blog-post__meta-separator" aria-hidden="true">・</span>
                            <span class="blog-post__categories">
                                <?php echo esc_html(implode(', ', wp_list_pluck($categories, 'name'))); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if (has_post_thumbnail()) : ?>
                    <figure class="blog-post__featured-image">
                        <?php the_post_thumbnail('large', array('class' => 'blog-post__featured-image-content')); ?>
                    </figure>
                <?php endif; ?>

                <div class="blog-post__content">
                    <?php the_content(); ?>
                </div>

                <?php $tags = get_the_tags(); ?>
                <?php if ($tags) : ?>
                    <footer class="blog-post__footer">
                        <div class="blog-post__tags" aria-label="<?php esc_attr_e('Tags', 'atnif'); ?>">
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($tag)); ?>">#<?php echo esc_html($tag->name); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </footer>
                <?php endif; ?>

                <?php
                $previous_post = get_previous_post();
                $next_post = get_next_post();
                ?>
                <?php if ($previous_post || $next_post) : ?>
                    <nav class="blog-post__navigation" aria-label="<?php esc_attr_e('Post navigation', 'atnif'); ?>">
                        <?php if ($previous_post) : ?>
                            <a class="blog-post__navigation-link blog-post__navigation-link--previous" href="<?php echo esc_url(atnif_blog_post_url($previous_post->ID)); ?>">
                                <span class="blog-post__navigation-label">← <?php esc_html_e('前の記事', 'atnif'); ?></span>
                                <span class="blog-post__navigation-title"><?php echo esc_html(get_the_title($previous_post)); ?></span>
                            </a>
                        <?php else : ?>
                            <span></span>
                        <?php endif; ?>

                        <?php if ($next_post) : ?>
                            <a class="blog-post__navigation-link blog-post__navigation-link--next" href="<?php echo esc_url(atnif_blog_post_url($next_post->ID)); ?>">
                                <span class="blog-post__navigation-label"><?php esc_html_e('次の記事', 'atnif'); ?> →</span>
                                <span class="blog-post__navigation-title"><?php echo esc_html(get_the_title($next_post)); ?></span>
                            </a>
                        <?php else : ?>
                            <span></span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <?php
        global $wp_query;

        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        ?>
        <section class="blog-post blog-post--not-found">
            <h1 class="blog-post__title"><?php esc_html_e('記事が見つかりません。', 'atnif'); ?></h1>
            <a class="blog-post__back" href="<?php echo esc_url(home_url('/blog/')); ?>">← <?php esc_html_e('ブログ一覧へ', 'atnif'); ?></a>
        </section>
    <?php endif; ?>
</main>

<?php
get_footer();
