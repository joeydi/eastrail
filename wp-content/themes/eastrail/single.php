<?php

get_header();

?>

<?php get_template_part('partials/page-header'); ?>

<section class="section-padding bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-xl-8" data-scroll-fade>
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
