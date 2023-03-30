<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$hide = get_sub_field('hide');
$category = get_sub_field('category');
$events = get_sub_field('events');
$placeholder_image = get_sub_field('placeholder_image');
$event_alignment = get_sub_field('event_alignment');
$vertical_alignment = get_sub_field('vertical_alignment');

if ($event_alignment == 'left') {
    $image_class = 'col-md-6';
    $content_class = 'col-md-6 col-xl-5 offset-xl-1';
} else {
    $image_class = 'col-md-6 offset-xl-1 order-md-2';
    $content_class = 'col-md-6 col-xl-5 order-md-1';
}

// If hide is set, only show upcoming events
if ($events && $hide) {
    $events = array_filter($events, function ($post) {
        return ET_Events::is_event_upcoming($post);
    });
}

// If there are no events after filtering, show upcoming
if (empty($events)) {
    $args = ['posts_per_page' => 1];

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

<section class="content-with-image <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div data-scroll-fade-children class="row <?php echo $vertical_alignment; ?>">
            <div class="<?php echo $image_class; ?> mb-40 mb-md-0">
                <?php if ($events) : foreach ($events as $post) : setup_postdata($post); ?>
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
                    <?php endforeach;
                    wp_reset_postdata();
                else : ?>
                    <picture class="aspect-landscape">
                        <?php echo wp_get_attachment_image($placeholder_image, 'landscape'); ?>
                    </picture>
                <?php endif; ?>
            </div>
            <div class="<?php echo $content_class; ?>">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</section>
