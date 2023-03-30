<?php

$label = get_sub_field('label');
$heading = get_sub_field('heading');
$cta = get_sub_field('cta');

$limit = 3;
$news = get_field('featured_posts') ?: [];
$count = count($news);
if ($count < $limit) {
    $recent = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => $limit - $count,
        'post__not_in' => wp_list_pluck($news, 'ID'),
    ]);
    $news = array_merge($news, $recent->posts);
}

?>

<?php if ($news) : ?>
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

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-30" data-scroll-fade-children>
                <?php foreach ($news as $post) : setup_postdata($post); ?>
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
