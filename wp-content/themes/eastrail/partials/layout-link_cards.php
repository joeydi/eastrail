<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$justify_content = get_sub_field('justify_content');
$cards = get_sub_field('cards');
$count = is_array($cards) ? count($cards) : 0;
$column_classes = [
    2 => 'col-sm-6',
    3 => 'col-md-4',
    4 => 'col-sm-6 col-lg-3',
];
$column_class = $column_classes[$count] ?: 'col-sm-6';

?>
<section class="link-cards <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <?php if ($content) : ?>
            <div class="row <?php echo $justify_content; ?>">
                <div class="col-lg-10 col-xl-8" data-scroll-fade-children>
                    <?php echo $content; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-30" data-scroll-fade-children>
            <?php if ($cards) : foreach ($cards as $card) : $link = $card['link']; ?>
                    <div class="<?php echo $column_class; ?>">
                        <a class="link-card" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" rel="<?php echo $link['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                            <span class="h4"><?php echo $link['title']; ?></span>
                        </a>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>
