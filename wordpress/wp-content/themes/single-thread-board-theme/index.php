<?php
get_header();

$board_posts = new WP_Query(array(
    'post_type' => STB_POST_TYPE,
    'post_status' => 'publish',
    'posts_per_page' => 30,
    'orderby' => 'date',
    'order' => 'DESC',
));
?>

<main class="board-page">
    <section class="board-intro" aria-labelledby="board-title">
        <div>
            <h1 id="board-title"><?php esc_html_e('One Thread Board', 'single-thread-board'); ?></h1>
            <p><?php esc_html_e('Post a short message with a drawing. Everything flows into this single shared thread.', 'single-thread-board'); ?></p>
        </div>
        <div class="board-count" aria-label="<?php esc_attr_e('Total posts', 'single-thread-board'); ?>">
            <strong><?php echo esc_html(stb_get_board_count()); ?></strong>
            <span><?php esc_html_e('posts', 'single-thread-board'); ?></span>
        </div>
    </section>

    <?php echo wp_kses_post(stb_status_message()); ?>

    <div class="board-layout">
        <aside class="board-composer" aria-labelledby="composer-title">
            <h2 id="composer-title"><?php esc_html_e('New Post', 'single-thread-board'); ?></h2>

            <form method="post" class="board-form" data-board-form>
                <?php wp_nonce_field('stb_submit_board_post', 'stb_nonce'); ?>
                <input type="hidden" name="stb_board_action" value="submit">
                <input type="hidden" name="stb_canvas_image" data-canvas-image>

                <div class="field">
                    <label for="stb-author-name"><?php esc_html_e('Name', 'single-thread-board'); ?></label>
                    <input id="stb-author-name" name="stb_author_name" type="text" maxlength="40" autocomplete="nickname" placeholder="<?php esc_attr_e('Anonymous', 'single-thread-board'); ?>">
                </div>

                <div class="field">
                    <label for="stb-message"><?php esc_html_e('Comment', 'single-thread-board'); ?></label>
                    <textarea id="stb-message" name="stb_message" maxlength="800" placeholder="<?php esc_attr_e('Write something...', 'single-thread-board'); ?>"></textarea>
                </div>

                <div class="canvas-area">
                    <div class="canvas-label"><?php esc_html_e('Drawing', 'single-thread-board'); ?></div>
                    <div class="canvas-tools">
                        <div class="tool-group">
                            <label class="tool-label" for="stb-color"><?php esc_html_e('Color', 'single-thread-board'); ?></label>
                            <div class="tool-row">
                                <input id="stb-color" type="color" value="#1d2528" data-canvas-color>
                            </div>
                        </div>
                        <div class="tool-group">
                            <label class="tool-label" for="stb-size"><?php esc_html_e('Size', 'single-thread-board'); ?></label>
                            <div class="tool-row">
                                <input id="stb-size" type="range" min="1" max="24" value="4" data-canvas-size>
                            </div>
                        </div>
                    </div>
                    <div class="canvas-wrap">
                        <canvas id="board-canvas" width="900" height="675" data-board-canvas></canvas>
                    </div>
                </div>

                <div class="composer-actions">
                    <button class="button button-danger" type="button" data-clear-canvas>
                        <?php esc_html_e('Clear', 'single-thread-board'); ?>
                    </button>
                    <button class="button button-primary" type="submit">
                        <?php esc_html_e('Post', 'single-thread-board'); ?>
                    </button>
                </div>
            </form>
        </aside>

        <section id="board-feed" class="board-feed" aria-labelledby="feed-title">
            <h2 id="feed-title"><?php esc_html_e('Thread', 'single-thread-board'); ?></h2>

            <?php if ($board_posts->have_posts()) : ?>
                <div class="post-list">
                    <?php
                    while ($board_posts->have_posts()) :
                        $board_posts->the_post();
                        $author_name = get_post_meta(get_the_ID(), STB_META_NAME, true);
                        $image_id = (int) get_post_meta(get_the_ID(), STB_META_IMAGE, true);
                        ?>
                        <article class="board-post">
                            <?php if ($image_id) : ?>
                                <div class="board-post__media">
                                    <?php echo wp_get_attachment_image($image_id, 'large'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="board-post__body">
                                <div class="board-post__meta">
                                    <span class="board-post__name"><?php echo esc_html($author_name ?: __('Anonymous', 'single-thread-board')); ?></span>
                                    <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                                        <?php echo esc_html(get_the_date()); ?> <?php echo esc_html(get_the_time()); ?>
                                    </time>
                                </div>

                                <?php if (trim(get_the_content()) !== '') : ?>
                                    <div class="board-post__content">
                                        <?php the_content(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-board">
                    <?php esc_html_e('No posts yet. Be the first to draw something.', 'single-thread-board'); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
get_footer();
