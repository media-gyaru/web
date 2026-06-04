<?php
get_header();
?>

<main id="main" class="site-main">
    <section class="hero" style="--hero-background: url('<?php echo esc_url(atnif_asset_url('images/key-visual.png')); ?>'); --hero-mobile-background: url('<?php echo esc_url(atnif_asset_url('images/mobile_mainvisual.png')); ?>');" aria-label="<?php esc_attr_e('Main visual', 'atnif-figma'); ?>">
        <img class="hero__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <?php
        $hero_image = atnif_image_mod('hero_image', 'hero__image', get_bloginfo('name'), 'full', array(
            'loading' => 'eager',
            'fetchpriority' => 'high',
        ));
        if ($hero_image) {
            echo $hero_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        ?>
    </section>

    <section id="story" class="section section--panel section--story" style="background-image: url('<?php echo esc_url(atnif_asset_url('images/story-bg.png')); ?>');" aria-labelledby="story-heading">
        <div class="section__inner section__inner--story">
            <div class="content-block">
                <h1 id="story-heading" class="section-heading">
                    <span>
                        <span class="section-heading__en">Story</span>
                        <span class="section-heading__ja">あらすじ</span>
                    </span>
                </h1>
                <p class="body-copy"><?php echo esc_html(atnif_mod('story_text')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
            </div>

            <?php
            $scenario_items = array(
                array(
                    'title' => atnif_mod('scenario_title1'),
                    'text' => atnif_mod('scenario_text1'),
                ),
                array(
                    'title' => atnif_mod('scenario_title2'),
                    'text' => atnif_mod('scenario_text2'),
                ),
            );
            ?>

            <article class="scenario" data-atnif-slider>
                <script type="application/json" data-slider-items><?php echo wp_json_encode($scenario_items); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
                <h2 class="scenario__title" data-slider-title><?php echo esc_html(atnif_mod('scenario_title1')); ?></h2>
                <div class="scenario__body">
                    <button class="arrow-button arrow-left" type="button" data-slider-prev>
                        <span class="screen-reader-text"><?php esc_html_e('Previous scenario', 'atnif-figma'); ?></span>
                    </button>
                    <p class="scenario__text" data-slider-text><?php echo esc_html(atnif_mod('scenario_text1')); ?></p>
                    <button class="arrow-button arrow-right" type="button" data-slider-next>
                        <span class="screen-reader-text"><?php esc_html_e('Next scenario', 'atnif-figma'); ?></span>
                    </button>
                </div>
                <div class="pager" aria-hidden="true">
                    <button class="pager__dot is-active" type="button" data-slider-dot="0"></button>
                    <button class="pager__dot" type="button" data-slider-dot="1"></button>
                </div>
            </article>
        </div>
    </section>

    <section id="character" class="section section--paper section--character" style="background-image: url('<?php echo esc_url(atnif_asset_url('images/character-bg.png')); ?>');" aria-labelledby="character-heading">
        <div class="section__inner">
            <h2 id="character-heading" class="section-heading">
                <span>
                    <span class="section-heading__en">Character</span>
                    <span class="section-heading__ja">登場人物</span>
                </span>
            </h2>

            <?php
            $character_items = array();
            for ($i = 1; $i <= 3; $i++) {
                $character_items[] = array(
                    'name' => atnif_mod('character_name' . $i),
                    'ruby' => atnif_mod('character_ruby' . $i),
                    'copy' => atnif_mod('character_copy' . $i),
                    'image' => atnif_image_url_mod('character_image' . $i),
                );
            }
            ?>

            <article class="character" data-atnif-character>
                <script type="application/json" data-character-items><?php echo wp_json_encode($character_items); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
                <div class="character__image-wrap" data-character-image-wrap>
                    <?php
                    $character_image = atnif_image_mod('character_image1', 'character__image', atnif_mod('character_name1'));
                    if ($character_image) {
                        echo $character_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    ?>
                </div>
                <div class="character__details">
                    <div class="character__name">
                        <span class="character__name-main" data-character-name><?php echo esc_html(atnif_mod('character_name1')); ?></span>
                        <span class="character__name-sub" data-character-ruby><?php echo esc_html(atnif_mod('character_ruby1')); ?></span>
                    </div>
                    <p class="character__copy" data-character-copy><?php echo atnif_textarea(atnif_mod('character_copy1')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                </div>
                <div class="character__select">
                    <p class="character__select-label">select</p>
                    <div class="character__swatches">
                        <?php for ($i = 1; $i <= 3; $i++) : ?>
                            <?php
                            $select_icon = atnif_image_url_mod('character_select_icon' . $i);
                            $button_classes = array('character__swatch');

                            if ($select_icon) {
                                $button_classes[] = 'character__swatch--has-icon';
                            }

                            if ($i === 1) {
                                $button_classes[] = 'is-active';
                            }

                            $select_icon_style = $select_icon ? '--character-select-icon: url(' . esc_url($select_icon) . ');' : '';
                            ?>
                            <button class="<?php echo esc_attr(implode(' ', $button_classes)); ?>" type="button" data-character-button="<?php echo esc_attr($i - 1); ?>" aria-pressed="<?php echo $i === 1 ? 'true' : 'false'; ?>"<?php echo $select_icon_style ? ' style="' . esc_attr($select_icon_style) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                                <span class="screen-reader-text"><?php echo esc_html(sprintf(__('Show character %d', 'atnif-figma'), $i)); ?></span>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section id="gallery" class="section section--panel section--gallery" style="background-image: url('<?php echo esc_url(atnif_asset_url('images/gallery-bg.png')); ?>');" aria-labelledby="gallery-heading">
        <div class="section__inner">
            <h2 id="gallery-heading" class="section-heading">
                <span>
                    <span class="section-heading__en">Gallery</span>
                    <span class="section-heading__ja">写真展示</span>
                </span>
            </h2>
            <div class="gallery-grid">
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <div class="gallery-grid__item">
                        <?php
                        $gallery_key = 'gallery_' . $i;
                        $gallery_image_url = atnif_image_url_mod($gallery_key);
                        $gallery_image = atnif_image_mod($gallery_key, '', sprintf(__('Gallery image %d', 'atnif-figma'), $i));
                        if ($gallery_image) {
                            echo $gallery_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            if (basename((string) parse_url($gallery_image_url, PHP_URL_PATH)) === 'comingsoon.png') {
                                echo '<span class="gallery-grid__coming-soon">' . esc_html__('Coming Soon', 'atnif-figma') . '</span>';
                            }
                        } elseif ($i === 1) {
                            esc_html_e('写真', 'atnif-figma');
                        }
                        ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <section id="special" class="section section--paper section--special" style="background-image: url('<?php echo esc_url(atnif_asset_url('images/special-bg.png')); ?>');" aria-labelledby="special-heading">
        <div class="section__inner">
            <h2 id="special-heading" class="section-heading">
                <span>
                    <span class="section-heading__en">Special</span>
                    <span class="section-heading__ja">おたのしみ</span>
                </span>
            </h2>
            <div class="special">
                <div class="special__banners">
                    <?php for ($i = 1; $i <= 2; $i++) : ?>
                        <?php $banner_url = atnif_mod('special_banner_' . $i . '_url'); ?>
                        <?php if ($banner_url) : ?>
                            <a class="special__banner" href="<?php echo esc_url($banner_url); ?>">
                                <?php echo esc_html(atnif_mod('special_banner_' . $i . '_label')); ?>
                            </a>
                        <?php else : ?>
                            <div class="special__banner"><?php echo esc_html(atnif_mod('special_banner_' . $i . '_label')); ?></div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <div class="special__content">
                    <h3 class="special__title"><?php echo esc_html(atnif_mod('special_title')); ?></h3>
                    <p class="special__copy"><?php echo atnif_textarea(atnif_mod('special_copy')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                    <div class="special__links">
                                        <a class="text-link" href="<?php echo esc_url(atnif_mod('x_url')); ?>" target="_blank" rel="noopener">X（旧Twitter）</a>
                        <a class="text-link" href="<?php echo esc_url(atnif_mod('tiktok_url')); ?>" target="_blank" rel="noopener">TikTok</a>
                        <a class="text-link" href="<?php echo esc_url(atnif_mod('youtube_url')); ?>" target="_blank" rel="noopener">Youtube</a>    
                </div>
                </div>
                <?php
                $special_image = atnif_image_mod('special_image', 'special__image', __('Special illustration', 'atnif-figma'));
                if ($special_image) {
                    echo $special_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                ?>
            </div>
        </div>
    </section>

    <section id="game" class="section section--panel section--game" style="background-image: url('<?php echo esc_url(atnif_asset_url('images/game-bg.png')); ?>');" aria-labelledby="game-heading">
        <div class="section__inner">
            <h2 id="game-heading" class="section-heading">
                <span>
                    <span class="section-heading__en">Game</span>
                    <span class="section-heading__ja">ゲーム</span>
                </span>
            </h2>
            <article class="game">
                <div class="game__media"><?php echo esc_html(atnif_mod('game_status')); ?></div>
                <div class="game__content">
                    <h3 class="game__title"><?php echo esc_html(atnif_mod('game_title')); ?></h3>
                    <?php $game_url = atnif_mod('game_link_url'); ?>
                    <?php if ($game_url) : ?>
                        <a class="game__link" href="<?php echo esc_url($game_url); ?>"><?php echo esc_html(atnif_mod('game_link_label')); ?></a>
                    <?php else : ?>
                        <span class="game__link"><?php echo esc_html(atnif_mod('game_link_label')); ?></span>
                    <?php endif; ?>
                    <p class="game__meta"><?php echo atnif_textarea(atnif_mod('game_meta')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                    <?php
                    $game_badge = atnif_image_mod('game_badge', 'game__badge', __('Game badge', 'atnif-figma'));
                    if ($game_badge) {
                        echo $game_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    ?>
                </div>
            </article>
        </div>
    </section>
</main>

<?php
get_footer();
