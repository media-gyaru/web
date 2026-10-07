<?php
/*
Template Name: Blog
*/

get_header();
?>

<main id="main" class="site-main blog-page">
    <section class="section section--paper section--blog" aria-labelledby="blog-heading">
        <div class="section__inner blog-page__inner">
            <h1 id="blog-heading" class="section-heading">
                <span>
                    <span class="section-heading__en">Blog</span>
                    <span class="section-heading__ja">ブログ</span>
                </span>
            </h1>

            <?php
            $paged = function_exists('atnif_blog_current_page') ? atnif_blog_current_page() : max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
            $blog_query = new WP_Query(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 9,
                'paged' => $paged,
            ));
            ?>

            <?php if ($blog_query->have_posts()) : ?>
                <div class="blog-list">
                    <?php while ($blog_query->have_posts()) : ?>
                        <?php $blog_query->the_post(); ?>
                        <?php get_template_part('template-parts/blog', 'card'); ?>
                    <?php endwhile; ?>
                </div>

                <?php
                $pagination = paginate_links(array(
                    'total' => $blog_query->max_num_pages,
                    'current' => $paged,
                    'prev_next' => false,
                    'type' => 'list',
                ));
                ?>

                <?php if ($pagination) : ?>
                    <nav class="blog-pagination" aria-label="<?php esc_attr_e('Blog pagination', 'atnif'); ?>">
                        <?php echo wp_kses_post($pagination); ?>
                    </nav>
                <?php endif; ?>

                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p class="blog-empty"><?php esc_html_e('記事はまだありません。', 'atnif'); ?></p>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php
get_footer();
