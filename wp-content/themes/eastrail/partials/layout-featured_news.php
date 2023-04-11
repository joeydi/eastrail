<?php

$label = get_sub_field('label');
$intro = get_sub_field('intro');
$cta = get_sub_field('cta');

$limit = 4;
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
    <section class="featured-news section-padding bg-topo" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
        <div class="container">
            <?php if ($intro) : ?>
                <div class="row justify-content-center mb-30 mb-md-40 mb-lg-50" data-scroll-fade>
                    <div class="col-md-10 col-lg-8 col-xl-6 text-center">
                        <?php echo $intro; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php $post = array_shift($news);
            setup_postdata($post); ?>
            <div class="mb-30" data-scroll-fade>
                <a class="post-excerpt" href="<?php the_permalink() ?>" target="<?php ET::the_target() ?>" rel="<?php ET::the_rel() ?>">
                    <div class="row g-0">
                        <div class="col-sm-6">
                            <picture class="aspect-block h-100">
                                <?php the_post_thumbnail('block'); ?>
                            </picture>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center">
                            <div class="content p-md-40 p-lg-60 p-xl-80">
                                <p class="meta text-dark-spruce mb-10"><?php echo ET::the_category_name(); ?></p>
                                <h3 class="text-dark-evergreen my-10"><?php the_title(); ?></h3>
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-30" data-scroll-fade-children>
                <?php foreach ($news as $i => $post) : setup_postdata($post); ?>
                    <div class="col <?php echo $i == 2 ? 'd-none d-md-block' : ''; ?>">
                        <a class="post-excerpt" href="<?php the_permalink() ?>" target="<?php ET::the_target() ?>" rel="<?php ET::the_rel() ?>">
                            <picture class="aspect-landscape">
                                <?php the_post_thumbnail('landscape'); ?>
                            </picture>
                            <div class="content p-20 p-lg-40 pt-lg-30">
                                <p class="meta text-dark-spruce mb-10"><?php echo ET::the_category_name(); ?></p>
                                <h3 class="text-dark-evergreen my-10"><?php the_title(); ?></h3>
                                <div class="medium">
                                    <?php the_excerpt(); ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach;
                wp_reset_postdata(); ?>
            </div>
    </section>
<?php endif; ?>
