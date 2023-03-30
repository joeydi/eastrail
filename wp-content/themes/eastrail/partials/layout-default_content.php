<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$width = get_sub_field('width');
$justify_content = get_sub_field('justify_content');
$background = get_sub_field('background');

?>
<section class="default-content <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row <?php echo $justify_content; ?>">
            <div class="<?php echo $width; ?>" data-scroll-fade-children>
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</section>
