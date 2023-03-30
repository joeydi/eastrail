<?php

$label = get_sub_field('label');
$image = get_sub_field('image');
$content = get_sub_field('content');

?>
<section class="banner-image overflow-hidden" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <?php if ($image) : ?>
        <picture class="aspect-landscape">
            <?php echo wp_get_attachment_image($image, 'landscape'); ?>
        </picture>
    <?php endif; ?>
    <?php if ($content) : ?>
        <div class="content" data-scroll-fade>
            <?php echo $content; ?>
        </div>
    <?php endif; ?>
</section>
