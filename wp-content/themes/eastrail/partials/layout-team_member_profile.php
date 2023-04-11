<?php

$label = get_sub_field('label');
$photo = get_sub_field('photo');
$name = get_sub_field('name');
$title = get_sub_field('title');
$alternate_title = get_sub_field('alternate_title');
$bio = get_sub_field('bio');

?>

<section class="team-members <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row align-items-center gx-md-60 gx-lg-80 gx-xl-100" data-scroll-fade-children>
            <div class="col-6 col-sm-4 col-lg-4">
                <picture class="aspect-square rounded-circle bg-grey-2 mb-30 mb-sm-0">
                    <?php echo wp_get_attachment_image($photo, 'square'); ?>
                </picture>
            </div>
            <div class="col-sm-8 col-lg-8 col-xl-6">
                <?php if ($name) : ?>
                    <h2 class="mb-10"><?php echo $name; ?></h2>
                <?php endif; ?>
                <?php if ($title) : ?>
                    <p class="meta text-dark-spruce"><?php echo $title; ?></p>
                <?php endif; ?>
                <?php if ($alternate_title) : ?>
                    <p class="mt-30 font-weight-semibold text-blue"><?php echo $alternate_title; ?></p>
                <?php endif; ?>
                <?php echo $bio; ?>
            </div>
        </div>
    </div>
</section>
