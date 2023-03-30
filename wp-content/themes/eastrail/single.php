<?php

get_header();

?>

<section class="page-header"></section>

<section class="breadcrumbs" data-scroll-fade>
    <div class="container">
        <a href="<?php echo site_url('/'); ?>">Home</a>
        <a href="<?php echo site_url('/about-us/'); ?>">About Us</a>
        <a href="<?php echo site_url('/about-us/newsroom/'); ?>">Newsroom</a>
        <span><?php the_title(); ?></span>
    </div>
</section>

<section class="section-margin">
    <div class="container">
        <div class="row">
            <div class="col-md-10 offset-md-1 col-xl-8 offset-xl-2 col-xxl-7" data-scroll-fade-children>
                <section>
                    <div class="container-full">
                        <div class="row">
                            <h1 class="h2"><?php the_title(); ?></h1>
                            <?php if (has_post_thumbnail() && !get_field('hide_featured_image')) : ?>
                                <picture class="aspect-widescreen mb-40 mb-lg-60">
                                    <?php the_post_thumbnail('widescreen'); ?>
                                </picture>
                            <?php endif; ?>
                            <div class="post-content">
                                <?php the_content(); ?>
                                <?php get_template_part('partials/flexible-content'); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>