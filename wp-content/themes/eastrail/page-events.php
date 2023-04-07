<?php

// Template Name: Events

$events = ET_Events::get_upcoming_events();

get_header();

?>

<?php get_template_part('partials/page-header'); ?>

<section class="post-grid section-padding">
    <div class="container">
        <?php if ($events->have_posts()) : ?>
            <div class="posts">
                <div class="row row-cols-1 row-cols-md-2 gy-40" data-scroll-fade-children>
                    <?php while ($events->have_posts()) : $events->the_post(); ?>
                        <div class="col">
                            <a href="<?php the_permalink() ?>" class="event-excerpt">
                                <picture class="aspect-widescreen">
                                    <?php the_post_thumbnail('widescreen'); ?>
                                </picture>
                                <div class="content-wrap">
                                    <?php $start_date = get_field('start_date');
                                    $start_time = strtotime($start_date); ?>
                                    <div class="date">
                                        <span class="month">
                                            <?php echo date('M', $start_time); ?>
                                        </span>
                                        <span class="day">
                                            <?php echo date('d', $start_time); ?>
                                        </span>
                                    </div>
                                    <div class="content">
                                        <h3 class="h4"><?php the_title(); ?></h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
                <div class="pagination mt-60 mt-lg-80" data-scroll-fade>
                    <?php wp_pagenavi([
                        'query' => $events,
                        'options' => [
                            'prev_text' => '<svg class="icon"><use xlink:href="#arrow-left" /></svg><span class="visually-hidden">Previous</span>',
                            'next_text' => '<span class="visually-hidden">Next</span> <svg class="icon"><use xlink:href="#arrow-right" /></svg>',
                        ]
                    ]); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="posts" data-scroll-fade>
                <h3>No current events</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_template_part('partials/flexible-content'); ?>

<?php get_footer(); ?>
