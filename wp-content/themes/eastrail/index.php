<?php

get_header();

?>

<section class="page-header page-header-small">
    <div class="container" data-scroll-fade>
        <div class="row">
            <div class="col-md-10 col-lg-8">
                <h1><?php the_archive_title(); ?></h1>
            </div>
        </div>
    </div>
</section>

<section class="archive section-margin">
    <div class="container">
        <?php if (have_posts()) : ?>
            <div class="posts">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-30" data-scroll-fade-children>
                    <?php while (have_posts()) : the_post(); ?>
                        <div class="col">
                            <a class="post-excerpt" href="<?php the_permalink() ?>" target="<?php ET::the_target() ?>" rel="<?php ET::the_rel() ?>">
                                <picture class="aspect-landscape">
                                    <?php the_post_thumbnail('landscape'); ?>
                                </picture>
                                <div class="content">
                                    <h3 class="h4"><?php the_title(); ?></h3>
                                </div>
                            </a>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
                <div class="pagination mt-60 mt-lg-80" data-scroll-fade>
                    <?php wp_pagenavi([
                        'options' => [
                            'prev_text' => '<svg class="icon"><use xlink:href="#arrow-left" /></svg><span class="visually-hidden">Previous</span>',
                            'next_text' => '<span class="visually-hidden">Next</span> <svg class="icon"><use xlink:href="#arrow-right" /></svg>',
                        ]
                    ]); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="posts" data-scroll-fade>
                <h3>Sorry, no posts were found.</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
