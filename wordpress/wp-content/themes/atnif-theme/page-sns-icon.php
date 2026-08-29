<?php
/**
 * Template Name: SNS Icon Distribution
 * Template Post Type: page
 */

get_header();

$sns_icons = array(
    array(
        'file' => 'sns-icon-nagi.png',
        'name' => 'なぎ',
    ),
    array(
        'file' => 'sns-icon-toki.png',
        'name' => '岡都トキ',
    ),
);
?>

<main id="main" class="sns-icon-page" style="--sns-icon-background: url('<?php echo esc_url(atnif_asset_url('images/character-bg.png')); ?>');">
    <section class="sns-icon-page__content" aria-labelledby="sns-icon-heading">
        <div class="sns-icon-page__inner">
            <h1 id="sns-icon-heading" class="section-heading sns-icon-page__heading">
                <span>
                    <span class="section-heading__en">SNS Icon</span>
                    <span class="section-heading__ja">SNSアイコン</span>
                </span>
            </h1>

            <div class="sns-icon-page__body">
                <div class="sns-icon-page__intro">
                    <p>SNSで使えるアイコンを配布中です。</p>
                    <p>ぜひご利用ください。</p>
                    <p>アイコンサムネイルをクリックし、画像を保存してご使用いただけます。</p>
                </div>

                <p class="sns-icon-page__notice">アイコンの二次配布、またアイコン以外の用途での使用は禁止いたします。</p>
            </div>

            <div class="sns-icon-page__downloads" aria-label="<?php esc_attr_e('Downloadable SNS icons', 'atnif'); ?>">
                <?php foreach ($sns_icons as $icon) : ?>
                    <?php $icon_url = atnif_asset_url('images/' . $icon['file']); ?>
                    <a class="sns-icon-page__download" href="<?php echo esc_url($icon_url); ?>" download aria-label="<?php echo esc_attr(sprintf(__('%s iconをダウンロード', 'atnif'), $icon['name'])); ?>">
                        <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr(sprintf(__('%sのSNSアイコン', 'atnif'), $icon['name'])); ?>" width="400" height="400">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
