<?php

$location_name = get_field('location_name');
$location_address = get_field('location_address');
$location_link = get_field('location_link');
$register_link = get_field('register_link');

get_header();

?>

<?php get_template_part('partials/page-header'); ?>

<section class="section-margin">
    <div class="container">
        <div class="row">
            <div class="order-md-2 col-md-5 col-lg-4 offset-lg-1 mb-60 mb-md-0" data-scroll-fade-children>
                <div class="sidebar-content-box">
                    <div class="icon-block">
                        <svg class="icon text-grey-3">
                            <use xlink:href="#clock" />
                        </svg>
                        <div>
                            <strong>Date and time</strong><br>
                            <?php ET_Events::the_date(); ?><br>
                            <?php ET_Events::the_time(); ?>
                        </div>
                    </div>

                    <?php if ($location_name || $location_address) : ?>
                        <hr>
                        <div class="icon-block">
                            <svg class="icon text-grey-3">
                                <use xlink:href="#map-marker" />
                            </svg>
                            <div>
                                <strong>Location:</strong>
                                <?php if ($location_link && ($location_name || $location_address)) : ?>
                                    <a href="<?php echo $location_link['url']; ?>" target="_blank" rel="noopener noreferrer">
                                        <strong><?php echo $location_name; ?></strong><br>
                                        <?php echo $location_address; ?>
                                    </a>
                                <?php elseif ($location_name || $location_address) : ?>
                                    <strong><?php echo $location_name; ?></strong><br>
                                    <?php echo $location_address; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($register_link) : ?>
                        <hr>
                        <a class="btn btn-outline-primary" href="<?php echo $register_link['url']; ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo $register_link['title']; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="order-md-1 col-md-7" data-scroll-fade-children>
                <?php if (has_post_thumbnail()) : ?>
                    <picture class="aspect-landscape mb-40 mb-lg-60">
                        <?php the_post_thumbnail('landscape'); ?>
                    </picture>
                <?php endif; ?>

                <div class="post-content">
                    <?php the_content(); ?>
                    <?php get_template_part('partials/flexible-content'); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
