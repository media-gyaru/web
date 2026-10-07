<?php
/**
 * 404テンプレート。
 *
 * @package atnif
 */

get_header();
?>

<main id="main" class="site-main blog-page">
    <section class="section section--paper section--blog" aria-labelledby="not-found-heading">
        <div class="section__inner blog-page__inner">
            <h1 id="not-found-heading" class="section-heading">
                <span>
                    <span class="section-heading__en">404</span>
                    <span class="section-heading__ja">ページが見つかりません</span>
                </span>
            </h1>
            <p class="blog-empty">お探しのページは移動または削除された可能性があります。</p>
            <p class="not-found-links">
                <a href="<?php echo esc_url(home_url('/')); ?>">トップページへ</a>
                <a href="<?php echo esc_url(home_url('/blog/')); ?>">ブログ一覧へ</a>
            </p>
        </div>
    </section>
</main>

<?php
get_footer();
