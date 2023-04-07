<?php

get_header();

?>

<?php get_template_part('partials/page-header'); ?>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row">
            <div class="col-md-10 offset-md-1 col-xl-8 offset-xl-2 col-xxl-7" data-scroll-fade-children>
                <section>
                    <div class="container-full">
                        <div class="row">
                            <?php if (has_post_thumbnail() && !get_field('hide_featured_image')) : ?>
                                <picture class="aspect-widescreen mb-40 mb-lg-60">
                                    <?php the_post_thumbnail('widescreen'); ?>
                                </picture>
                            <?php endif; ?>
                            <div class="post-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
