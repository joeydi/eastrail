<?php

$label = get_sub_field('label');
$image = get_sub_field('image');
$media_url = get_sub_field('media_url');
$content = get_sub_field('content');
$image_alignment = get_sub_field('image_alignment');
$image_size = get_sub_field('image_size');

if ($image_alignment == 'left') {
    $image_class = 'col-md-6';
    $content_class = 'col-md-6';
} else {
    $image_class = 'col-md-6 order-md-2';
    $content_class = 'col-md-6 order-md-1';
}

?>
<section class="content-with-image align-<?php echo $image_alignment; ?> <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div data-scroll-fade-children class="row">
            <div class="<?php echo $image_class; ?> mb-40 mb-md-0">
                <div class="image">
                    <?php if ($media_url) : ?>
                        <?php echo wp_oembed_get($media_url); ?>
                    <?php elseif ($image) : ?>
                        <picture class="h-100 aspect-<?php echo $image_size; ?> bg-grey-2">
                            <?php echo wp_get_attachment_image($image, $image_size); ?>
                        </picture>
                    <?php endif; ?>
                </div>
            </div>
            <div class="<?php echo $content_class; ?>">
                <div class="content">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>
</section>
