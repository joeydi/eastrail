<?php

$label = get_sub_field('label');
$heading = get_sub_field('heading');
$cta = get_sub_field('cta');
$hide = get_sub_field('hide');
$show_featured_image = get_sub_field('show_featured_image');
$category = get_sub_field('category');
$events = get_sub_field('events');

// If hide is set, only show upcoming events
if ($events && $hide) {
    $events = array_filter($events, function ($post) {
        return ET_Events::is_event_upcoming($post);
    });
}

// If there are no events after filtering, show upcoming
if (empty($events)) {
    $args = ['posts_per_page' => 2];

    if ($category) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'event_category',
                'field'    => 'slug',
                'terms'    => $category->slug,
            ]
        ];
    }

    $events = ET_Events::get_upcoming_events($args)->posts;
}

?>

<?php if ($events) : ?>
    <section class="events section-margin <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
        <div class="container">
            <?php if ($heading || $cta) : ?>
                <div class="row align-items-end mb-30 mb-md-40 mb-lg-50" data-scroll-fade-children>
                    <div class="col-md-8">
                        <?php if ($heading) : ?>
                            <h2><?php echo $heading; ?></h2>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-md-end d-none d-md-block">
                        <?php if ($cta) : ?>
                            <a class="arrow-link" href="<?php echo $cta['url']; ?>" target="<?php echo $cta['target']; ?>" rel="<?php echo $cta['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                                <?php echo $cta['title']; ?>
                                <svg class="icon">
                                    <use xlink:href="#arrow-right" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row row-cols-1 row-cols-md-2 gy-40" data-scroll-fade-children>
                <?php foreach ($events as $post) : setup_postdata($post); ?>
                    <div class="col">
                        <a href="<?php the_permalink() ?>" class="event-excerpt">
                            <?php if ($show_featured_image) : ?>
                                <picture class="aspect-widescreen">
                                    <?php the_post_thumbnail('widescreen'); ?>
                                </picture>
                            <?php endif; ?>
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
                <?php endforeach;
                wp_reset_postdata(); ?>
            </div>

            <?php if ($cta) : ?>
                <div class="mt-20 d-md-none" data-scroll-fade>
                    <a class="arrow-link" href="<?php echo $cta['url']; ?>" target="<?php echo $cta['target']; ?>" rel="<?php echo $cta['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                        <?php echo $cta['title']; ?>
                        <svg class="icon">
                            <use xlink:href="#arrow-right" />
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
    </section>
<?php endif; ?>
