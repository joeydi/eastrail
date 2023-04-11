<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$card_layout = get_sub_field('card_layout');
$image_size = get_sub_field('image_size');
$background = get_sub_field('background');
$columns = get_sub_field('columns');
$cards = get_sub_field('cards');

?>
<section class="feature-cards <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <?php if ($content) : ?>
            <div class="row justify-content-center mb-30 mb-md-40 mb-lg-50">
                <div class="col-md-10 col-xl-8" data-scroll-fade>
                    <?php echo $content; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row gy-40" data-scroll-fade-children>
            <?php if ($cards) : foreach ($cards as $card) : $link = $card['link']; ?>
                    <div class="<?php echo $columns; ?>">
                        <?php if ($link) : ?>
                            <a class="feature-card <?php echo $card_layout; ?> <?php echo !$background ? 'bg-sand' : ''; ?>" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" rel="<?php echo $link['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                                <?php if ($image_size !== 'icon') : ?>
                                    <div class="image">
                                        <picture class="aspect-<?php echo $image_size; ?> bg-grey-2">
                                            <?php echo wp_get_attachment_image($card['image'], $image_size); ?>
                                        </picture>
                                    </div>
                                <?php endif; ?>
                                <div class="content medium">
                                    <?php if ($image_size == 'icon') : ?>
                                        <div class="icon">
                                            <picture class="aspect-square contain bg-transparent">
                                                <?php echo wp_get_attachment_image($card['image'], 'full'); ?>
                                            </picture>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($card['heading']) : ?>
                                        <h3 class="text-evergreen mb-10"><?php echo $card['heading']; ?></h3>
                                    <?php endif; ?>
                                    <?php echo $card['content']; ?>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="feature-card <?php echo $card_layout; ?> <?php echo !$background ? 'bg-sand' : ''; ?>">
                                <?php if ($image_size !== 'icon') : ?>
                                    <div class="image">
                                        <picture class="aspect-<?php echo $image_size; ?> bg-grey-2">
                                            <?php echo wp_get_attachment_image($card['image'], $image_size); ?>
                                        </picture>
                                    </div>
                                <?php endif; ?>
                                <div class="content medium">
                                    <?php if ($image_size == 'icon') : ?>
                                        <div class="icon">
                                            <picture class="aspect-square contain bg-transparent">
                                                <?php echo wp_get_attachment_image($card['image'], 'full'); ?>
                                            </picture>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($card['heading']) : ?>
                                        <h3 class="mb-10"><?php echo $card['heading']; ?></h3>
                                    <?php endif; ?>
                                    <?php echo $card['content']; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>
