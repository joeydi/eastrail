<?php

$label = get_sub_field('label');
$columns = get_sub_field('columns') ?: [];
$column_gutter = get_sub_field('column_gutter');
$vertical_alignment = get_sub_field('vertical_alignment');

$count = count($columns);
if ($count == 4) {
    $class = 'col-sm-6 col-lg-3 mb-30 mb-lg-0';
} elseif ($count == 3) {
    $class = 'col-md-4 mb-30 mb-md-0';
} elseif ($count == 2) {
    $class = 'col-md-6 mb-30 mb-md-0';
} else {
    $class = 'col-12';
}

?>
<section class="content-columns <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row <?php echo $column_gutter; ?> <?php echo $vertical_alignment; ?>" data-scroll-fade-children>
            <?php foreach ($columns as $column) : ?>
                <div class="<?php echo $class; ?>">
                    <?php echo $column['content']; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
