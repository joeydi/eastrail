<?php if (have_rows('page_header')) : while (have_rows('page_header')) : the_row(); ?>
        <?php if (get_row_layout() == 'small') : ?>
            <section class="page-header page-header-small">
                <div class="container" data-scroll-fade>
                    <div class="row">
                        <div class="col-md-10 col-lg-8">
                            <?php $heading = get_sub_field('heading') ?: get_the_title(); ?>
                            <h1><?php echo $heading; ?></h1>
                        </div>
                    </div>
                </div>
            </section>
        <?php elseif (get_row_layout() == 'medium') : ?>
            <section class="page-header page-header-medium">
                <?php if ($image = get_sub_field('image')) : ?>
                    <picture class="aspect bg-grey-1">
                        <?php echo wp_get_attachment_image($image, 'landscape'); ?>
                    </picture>
                <?php endif; ?>
                <div class="container" data-scroll-fade>
                    <div class="row">
                        <div class="col-md-7 col-lg-6">
                            <?php $heading = get_sub_field('heading') ?: get_the_title(); ?>
                            <h1><?php echo $heading; ?></h1>
                        </div>
                    </div>
                </div>
            </section>
        <?php elseif (get_row_layout() == 'large') : ?>
            <section class="page-header page-header-large">
                <?php $images = get_sub_field('images'); ?>
                <?php if (count($images) > 1) : ?>
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($images as $image) : ?>
                                <div class="swiper-slide">
                                    <picture class="aspect-block bg-black">
                                        <?php echo wp_get_attachment_image($image, 'block'); ?>
                                    </picture>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <picture class="aspect-block bg-black">
                        <?php echo wp_get_attachment_image($image, 'block'); ?>
                    </picture>
                <?php endif; ?>
                <div class="container" data-scroll-fade>
                    <div class="row">
                        <div class="col-md-7 col-lg-6">
                            <?php $heading = get_sub_field('heading') ?: get_the_title(); ?>
                            <h1><?php echo $heading; ?></h1>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endwhile; ?>
<?php else : ?>
    <section class="page-header page-header-small">
        <div class="container" data-scroll-fade>
            <div class="row">
                <div class="col-md-10 col-lg-8">
                    <?php $heading = get_sub_field('heading') ?: get_the_title(); ?>
                    <h1><?php the_title(); ?></h1>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
