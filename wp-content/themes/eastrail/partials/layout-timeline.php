<?php

$label = get_sub_field('label');
$intro = get_sub_field('intro');
$milestones = get_sub_field('milestones');

?>
<section class="default-content section-margin" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <?php if ($intro) : ?>
            <div class="row justify-content-center mb-30 mb-md-40 mb-lg-50" data-scroll-fade>
                <div class="col-md-10 col-lg-8 col-xl-6 text-center">
                    <?php echo $intro; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($milestones) : ?>
            <div class="timeline" data-scroll-fade data-scroll-fade-children>
                <?php foreach ($milestones as $i => $milestone) : ?>
                    <div class="milestone mb-30 mb-md-40 <?php echo $i % 2 ? 'even' : 'odd'; ?>">
                        <div class="content">
                            <p class="meta text-spruce mb-10">
                                <?php echo $milestone['date']; ?>
                            </p>
                            <div>
                                <?php echo $milestone['content']; ?>
                            </div>
                        </div>
                        <div class="image">
                            <picture class="aspect-landscape">
                                <?php echo wp_get_attachment_image($milestone['image'], 'medium'); ?>
                            </picture>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
