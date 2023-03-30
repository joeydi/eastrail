<?php

$label = get_sub_field('label');
$images = get_sub_field('images');

?>
<?php if ($images) : ?>
    <section class="my-10 <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
        <div class="container">
            <div class="swiper image-slider">
                <div class="swiper-wrapper">
                    <?php foreach ($images as $image) : ?>
                        <div class="swiper-slide">
                            <picture class="aspect-block">
                                <?php echo wp_get_attachment_image($image, 'block'); ?>
                            </picture>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev">
                    <svg class="icon">
                        <use xlink:href="#arrow-left" />
                    </svg>
                </div>
                <div class="swiper-button-next">
                    <svg class="icon">
                        <use xlink:href="#arrow-right" />
                    </svg>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
