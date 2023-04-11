<?php

$label = get_sub_field('label');
$intro = get_sub_field('intro');
$groups = get_sub_field('groups');

?>
<section class="section-margin" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <?php if ($intro) : ?>
            <div class="row justify-content-center mb-30 mb-md-40 mb-lg-50" data-scroll-fade>
                <div class="col-md-10 col-lg-8 col-xl-6 text-center">
                    <?php echo $intro; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row gy-40 gy-lg-60 gy-xl-80" data-scroll-fade-children>
            <?php if ($groups) : foreach ($groups as $group) : $count = $group['logos'] ? count($group['logos']) : 0; ?>
                    <div class="col-md-4">
                        <div class="p-20 p-md-30 p-lg-40 bg-sand rounded">
                            <?php if ($group['title']) : ?>
                                <p class="meta text-dark-spruce text-center"><?php echo $group['title']; ?></p>
                            <?php endif; ?>
                            <div class="row justify-content-center gy-20 gx-30 gx-lg-40 align-items-center row-cols-2">
                                <?php if ($group['logos']) : foreach ($group['logos'] as $logo) : ?>
                                        <div class="col">
                                            <div class="mx-auto" style="max-width: 160px">
                                                <?php if ($logo['link']) : $link = $logo['link']; ?>
                                                    <a href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" rel="<?php echo $link['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                                                        <picture class="aspect-landscape contain bg-transparent">
                                                            <?php echo wp_get_attachment_image($logo['image'], 'medium'); ?>
                                                        </picture>
                                                    </a>
                                                <?php else : ?>
                                                    <picture class="aspect-landscape contain bg-transparent">
                                                        <?php echo wp_get_attachment_image($logo['image'], 'medium'); ?>
                                                    </picture>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($count < 3) : ?>
                                            <div class="w-100 m-0"></div>
                                        <?php endif; ?>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>
