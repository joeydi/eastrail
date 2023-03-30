<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$sidebar = get_sub_field('sidebar');

$sidebar_display = get_sub_field('sidebar_display') ?: '';

$sidebar_classes = 'col-md-5 col-lg-4 offset-lg-1';
$sidebar_order_classes = $sidebar_display ? ' order-xs-2 order-md-2' : ' order-xs-1 order-md-2';
$margin_classes = $sidebar_display ? ' mt-60 mt-md-0' : ' mb-60 mb-md-0';

$content_classes = 'col-md-7';
$content_order_classes = $sidebar_display ? ' order-xs-1 order-md-1 ' : ' order-xs-2 order-md-1 ';

?>
<section class="content-with-image <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row" data-scroll-fade-children>
            <div class="<?php echo $sidebar_classes . $sidebar_order_classes . $margin_classes; ?>">
                <?php if (have_rows('sidebar')) : while (have_rows('sidebar')) : the_row(); ?>

                        <?php if (get_row_layout() == 'content_box') : ?>
                            <div class="sidebar-content-box">
                                <?php the_sub_field('content'); ?>
                            </div>
                        <?php elseif (get_row_layout() == 'icon_list') : ?>
                            <div class="sidebar-icon-list">
                                <?php $items = get_sub_field('items'); ?>
                                <?php if ($items) : foreach ($items as $item) : ?>
                                        <div class="mb-40 d-flex">
                                            <div class="icon me-16">
                                                <?php echo wp_get_attachment_image($item['icon'], 'full'); ?>
                                            </div>
                                            <div class="content">
                                                <?php echo $item['content']; ?>
                                            </div>
                                        </div>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        <?php elseif (get_row_layout() == 'image') : ?>
                            <div class="sidebar-image">
                                <?php if ($image = get_sub_field('image')) : $image_size = get_sub_field('image_size'); ?>
                                    <picture class="aspect-<?php echo $image_size; ?> bg-grey-2">
                                        <?php echo wp_get_attachment_image($image, $image_size); ?>
                                    </picture>
                                <?php endif; ?>
                            </div>
                        <?php elseif (get_row_layout() == 'embed') : ?>
                            <div class="sidebar-embed">
                                <?php the_sub_field('html'); ?>
                            </div>
                        <?php endif; ?>

                <?php endwhile;
                endif; ?>
            </div>
            <div class="<?php echo $content_classes . $content_order_classes; ?>">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</section>
