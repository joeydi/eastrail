<?php if (have_rows('page_header')) : while (have_rows('page_header')) : the_row(); ?>
        <?php if (get_row_layout() == 'basic') : ?>
            <section class="page-header page-header-basic text-center">
                <div class="container">
                    <div class="row justify-content-center" data-scroll-fade-children>
                        <div class="col-10">
                            <?php $heading = get_sub_field('heading') ?: get_the_title(); ?>
                            <h1><?php echo $heading; ?></h1>
                        </div>
                        <?php if ($subheading = get_sub_field('subheading')) : ?>
                            <div class="col-8 mt-30 font-weight-bold">
                                <?php echo $subheading; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php elseif (get_row_layout() == 'image') : ?>
            <section class="page-header page-header-image text-center">
                <div class="picture-wrapper">
                    <?php if ($image = get_sub_field('image')) : ?>
                        <picture class="aspect bg-evergreen">
                            <?php echo wp_get_attachment_image($image, 'landscape'); ?>
                        </picture>
                    <?php endif; ?>
                    <div class="container">
                        <div class="row justify-content-center" data-scroll-fade-children>
                            <div class="col-10">
                                <?php $heading = get_sub_field('heading') ?: get_the_title(); ?>
                                <h1><?php echo $heading; ?></h1>
                            </div>
                            <?php if ($subheading = get_sub_field('subheading')) : ?>
                                <div class="col-8 mt-30 font-weight-bold">
                                    <?php echo $subheading; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endwhile; ?>
<?php else : ?>
    <section class="page-header page-header-basic text-center">
        <div class="container">
            <div class="row justify-content-center" data-scroll-fade-children>
                <div class="col-10">
                    <h1><?php the_title(); ?></h1>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
