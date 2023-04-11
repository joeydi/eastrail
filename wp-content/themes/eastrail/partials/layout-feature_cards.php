<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$content_cta = get_sub_field('content_cta');
$card_layout = get_sub_field('card_layout');
$image_size = get_sub_field('image_size');
$background = get_sub_field('background');
$cards = get_sub_field('cards');
$count = is_array($cards) ? count($cards) : 0;
$column_class = in_array($count, [1, 2, 4]) ? 'col-sm-6' : 'col-sm-6 col-lg-4';

?>
<section class="feature-cards <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <?php if ($content || $content_cta) : ?>
            <div class="row align-items-end mb-30 mb-md-40 mb-lg-50" data-scroll-fade-children>
                <div class="col-md-8">
                    <?php echo $content; ?>
                </div>
                <div class="col-md-4 text-md-end d-none d-md-block">
                    <?php if ($content_cta) : ?>
                        <a class="arrow-link" href="<?php echo $content_cta['url']; ?>" target="<?php echo $content_cta['target']; ?>" rel="<?php echo $content_cta['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                            <?php echo $content_cta['title']; ?>
                            <svg class="icon">
                                <use xlink:href="#arrow-right" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row gy-40" data-scroll-fade-children>
            <?php if ($cards) : foreach ($cards as $card) : $link = $card['link']; ?>
                    <div class="<?php echo $column_class; ?>">
                        <?php if ($link) : ?>
                            <a class="feature-card <?php echo $card_layout; ?>" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" rel="<?php echo $link['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                                <?php if ($image_size !== 'icon') : ?>
                                    <div class="image <?php echo !$background ? 'mb-30' : ''; ?>">
                                        <picture class="aspect-<?php echo $image_size; ?> bg-grey-2">
                                            <?php echo wp_get_attachment_image($card['image'], $image_size); ?>
                                        </picture>
                                    </div>
                                <?php endif; ?>
                                <div class="content medium <?php echo !$background ? 'p-0' : ''; ?>">
                                    <?php if ($image_size == 'icon') : ?>
                                        <div class="icon">
                                            <picture class="aspect-square contain bg-transparent">
                                                <?php echo wp_get_attachment_image($card['image'], 'full'); ?>
                                            </picture>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($card['heading']) : ?>
                                        <h3 class="h4 text-maroon"><?php echo $card['heading']; ?></h3>
                                    <?php endif; ?>
                                    <?php echo $card['content']; ?>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="feature-card <?php echo $card_layout; ?>">
                                <?php if ($image_size !== 'icon') : ?>
                                    <div class="image <?php echo !$background ? 'mb-30' : ''; ?>">
                                        <picture class="aspect-<?php echo $image_size; ?> bg-grey-2">
                                            <?php echo wp_get_attachment_image($card['image'], $image_size); ?>
                                        </picture>
                                    </div>
                                <?php endif; ?>
                                <div class="content medium <?php echo !$background ? 'p-0' : ''; ?>">
                                    <?php if ($image_size == 'icon') : ?>
                                        <div class="icon">
                                            <picture class="aspect-square contain bg-transparent">
                                                <?php echo wp_get_attachment_image($card['image'], 'full'); ?>
                                            </picture>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($card['heading']) : ?>
                                        <h3><?php echo $card['heading']; ?></h3>
                                    <?php endif; ?>
                                    <?php echo $card['content']; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>

        <?php if ($content_cta) : ?>
            <div class="mt-20 d-md-none" data-scroll-fade>
                <a class="arrow-link" href="<?php echo $content_cta['url']; ?>" target="<?php echo $content_cta['target']; ?>" rel="<?php echo $content_cta['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                    <?php echo $content_cta['title']; ?>
                    <svg class="icon">
                        <use xlink:href="#arrow-right" />
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
