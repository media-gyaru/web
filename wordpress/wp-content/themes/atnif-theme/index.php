<?php
/**
 * 投稿アーカイブ用のフォールバックテンプレート。
 *
 * @package atnif
 */

get_header();

$heading_en = 'Blog';
$heading_ja = 'ブログ';
$archive_description = '';

if (is_category()) {
    $heading_en = 'Category';
    $heading_ja = single_cat_title('', false);
    $archive_description = category_description();
} elseif (is_author()) {
    $heading_en = 'Author';
    $heading_ja = get_the_author();
    $archive_description = get_the_author_meta('description');
} elseif (is_tag()) {
    $heading_en = 'Tag';
    $heading_ja = single_tag_title('', false);
} elseif (is_archive()) {
    $heading_en = 'Archive';
    $heading_ja = wp_strip_all_tags(get_the_archive_title());
}
?>

<main id="main" class="site-main blog-page">
    <section class="section section--paper section--blog" aria-labelledby="archive-heading">
        <div class="section__inner blog-page__inner">
            <h1 id="archive-heading" class="section-heading">
                <span>
                    <span class="section-heading__en"><?php echo esc_html($heading_en); ?></span>
                    <span class="section-heading__ja"><?php echo esc_html($heading_ja); ?></span>
                </span>
            </h1>

            <?php if ($archive_description) : ?>
                <div class="blog-archive-description">
                    <?php if (is_author()) : ?>
                        <?php echo wp_kses_post(wpautop($archive_description)); ?>
                    <?php else : ?>
                        <?php echo wp_kses_post($archive_description); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (have_posts()) : ?>
                <div class="blog-list">
                    <?php while (have_posts()) : ?>
                        <?php the_post(); ?>
                        <?php get_template_part('template-parts/blog', 'card'); ?>
                    <?php endwhile; ?>
                </div>

                <?php
                $pagination = paginate_links(array(
                    'prev_next' => false,
                    'type' => 'list',
                ));
                ?>

                <?php if ($pagination) : ?>
                    <nav class="blog-pagination" aria-label="<?php esc_attr_e('記事一覧のページ送り', 'atnif'); ?>">
                        <?php echo wp_kses_post($pagination); ?>
                    </nav>
                <?php endif; ?>
            <?php else : ?>
                <p class="blog-empty"><?php esc_html_e('記事はまだありません。', 'atnif'); ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
