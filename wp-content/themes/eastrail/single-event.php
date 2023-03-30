<?php

$location = get_field('location');
$location_name = get_field('location_name');
$location_address = get_field('location_address');
$get_directions_link = get_field('get_directions_link');

get_header();

?>

<section class="page-header"></section>

<section class="breadcrumbs" data-scroll-fade>
    <div class="container">
        <a href="<?php echo site_url('/'); ?>">Home</a>
        <a href="<?php echo site_url('/about-us/'); ?>">About Us</a>
        <a href="<?php echo site_url('/about-us/events/'); ?>">Events</a>
        <span><?php the_title(); ?></span>
    </div>
</section>

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

                    <hr>
                    <div class="icon-block">
                        <svg class="icon text-grey-3">
                            <use xlink:href="#map-marker" />
                        </svg>

                        <div>
                            <strong>Location:</strong>

                            <?php if ($location) : $link = get_field('get_directions_link', $location); ?>
                                <a href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer">
                                    <strong><?php echo get_the_title($location); ?></strong><br>
                                    <?php the_field('address', $location); ?>
                                </a>
                            <?php elseif ($get_directions_link && ($location_name || $location_address)) : ?>
                                <a href="<?php echo $get_directions_link; ?>" target="_blank" rel="noopener noreferrer">
                                    <strong><?php echo $location_name; ?></strong><br>
                                    <?php echo $location_address; ?>
                                </a>
                            <?php elseif ($location_name || $location_address) : ?>
                                <strong><?php echo $location_name; ?></strong><br>
                                <?php echo $location_address; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr>
                    <a href="" class="btn btn-outline-primary">Add to calendar</a>
                </div>
            </div>
            <div class="order-md-1 col-md-7" data-scroll-fade-children>
                <h1 class="h2"><?php the_title(); ?></h1>

                <?php if (has_post_thumbnail()) : ?>
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
    </div>
</section>

<?php get_footer(); ?>
