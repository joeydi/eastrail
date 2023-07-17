<?php

$image = get_field('page_not_found_image', 'options');
$content = get_field('page_not_found_content', 'options');

get_header();

?>

<section class="page-header page-header-basic text-center">
    <div class="container">
        <div class="row justify-content-center" data-scroll-fade-children>
            <div class="col-10">
                <h1>Page Not Found</h1>
            </div>
        </div>
    </div>
</section>

<section class="content-with-image align-left <?php echo ET::section_classes(); ?>" aria-label="Page Not Found">
    <div class="container">
        <div class="row" data-scroll-fade-children>
            <div class="col-md-6 mb-40 mb-md-0">
                <div class="image">
                    <?php if ($image) : ?>
                        <picture class="h-100 aspect-block bg-grey-2">
                            <?php echo wp_get_attachment_image($image, 'block'); ?>
                        </picture>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="content">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<?php get_footer(); ?>
