<?php

$label = get_sub_field('label');
$stories = get_sub_field('stories');

?>
<section class="<?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row gy-40 gy-lg-60 gy-xl-80" data-scroll-fade-children>
            <?php if ($stories) : foreach ($stories as $story) : ?>
                    <div class="col-sm-6 col-md-4">
                        <?php if ($story['image']) : ?>
                            <picture class="aspect-landscape mb-10">
                                <?php echo wp_get_attachment_image($story['image'], 'landscape'); ?>
                            </picture>
                        <?php endif; ?>
                        <svg class="icon text-orange h4 mt-0 mb-30">
                            <use xlink:href="#quote" />
                        </svg>
                        <?php echo $story['content']; ?>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>
