<?php

$social_links = [
    [
        'icon' => '#facebook',
        'title' => 'Facebook',
        'url' => get_field('facebook_url', 'options'),
    ],
    [
        'icon' => '#twitter',
        'title' => 'Twitter',
        'url' => get_field('twitter_url', 'options'),
    ],
    [
        'icon' => '#linkedin',
        'title' => 'LinkedIn',
        'url' => get_field('linkedin_url', 'options'),
    ],
];

?>

<?php get_template_part('partials/footer-cta'); ?>

</main>

<footer>
    <div class="container">
        <a class="logo" href="<?php echo site_url(); ?>" title="<?php bloginfo('sitename'); ?>">
            <img src="<?php echo ET::theme_url('static/img/eastrail-partners-logo.jpg'); ?>" alt="Eastrail Partners">
        </a>

        <div class="row">
            <div class="col-md-8 order-md-2 col-xl-6">
                <div class="d-sm-flex justify-content-between">
                    <?php wp_nav_menu(['menu' => 'Footer Column 1', 'container' => false]); ?>
                    <?php wp_nav_menu(['menu' => 'Footer Column 2', 'container' => false]); ?>
                    <?php wp_nav_menu(['menu' => 'Footer Column 3', 'container' => false]); ?>
                </div>
            </div>
            <div class="col-md-4 order-md-1 col-xl-6">
                <?php the_field('footer_contact_info', 'options'); ?>
                <?php wp_nav_menu(['menu' => 'Social', 'menu_class' => 'social', 'container' => false]); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-xl-6">
                <div class="disclaimer">
                    <?php the_field('footer_disclaimer', 'options'); ?>
                </div>
            </div>
            <div class="col-md-8 col-xl-6">
                <?php wp_nav_menu(['menu' => 'Footer Utility', 'menu_class' => 'utility', 'container' => false]); ?>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
<?php the_field('body_close_tracking_codes', 'options'); ?>
</body>

</html>
