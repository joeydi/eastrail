<?php

$social_links = [
    [
        'icon' => '#twitter',
        'title' => 'Twitter',
        'url' => get_field('twitter_url', 'options'),
    ],
    [
        'icon' => '#facebook',
        'title' => 'Facebook',
        'url' => get_field('facebook_url', 'options'),
    ],
    [
        'icon' => '#youtube',
        'title' => 'YouTube',
        'url' => get_field('youtube_url', 'options'),
    ],
    [
        'icon' => '#instagram',
        'title' => 'Instagram',
        'url' => get_field('instagram_url', 'options'),
    ],
];

?></main>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-lg-2 col-xxl-3">
                <?php wp_nav_menu(['menu' => 'Footer', 'container' => false]); ?>
            </div>

            <div class="col-md-4 col-lg-3 col-xxl-3 mb-50">
                <?php the_field('footer_column_1', 'options'); ?>
            </div>

            <div class="col-md-4 col-lg-3 col-xxl-3 mb-50">
                <?php the_field('footer_column_2', 'options'); ?>
            </div>

            <div class="col-md-12 col-lg-4 col-xxl-3 mb-50">
                <a class="logo" href="<?php echo site_url(); ?>" title="<?php bloginfo('sitename'); ?>">
                    <img src="<?php echo ET::theme_url('static/img/eastrail-partners-logo.jpg'); ?>" alt="Eastrail Partners">
                </a>

                <div class="social-links">
                    <?php foreach ($social_links as $link) : if ($link['url']) : ?>
                            <a title="<?php echo $link['title']; ?>" href="<?php echo $link['url']; ?>" target="_blank" rel="noopener noreferrer">
                                <svg>
                                    <use xlink:href="<?php echo $link['icon']; ?>" />
                                </svg>
                            </a>
                    <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
        <div class="disclaimer">
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('sitename'); ?>. All Rights Reserved.</p>
                <?php wp_nav_menu(['menu' => 'Footer Utility', 'container' => false]); ?>
            </div>
            <?php the_field('footer_disclaimer', 'options'); ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
<?php the_field('body_close_tracking_codes', 'options'); ?>
</body>

</html>
