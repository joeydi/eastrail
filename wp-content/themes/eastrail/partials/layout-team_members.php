<?php

$label = get_sub_field('label');
$intro = get_sub_field('intro');
$members = get_sub_field('members');
$count = is_array($members) ? count($members) : 0;
$column_class = $count >= 6 ? 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4' : 'row-cols-1 row-cols-lg-2'

?>

<section class="team-members <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <?php if ($intro) : ?>
            <div class="row justify-content-center mb-40 mb-md-60 mb-lg-80" data-scroll-fade>
                <div class="col-sm-10 col-md-9 col-lg-8 col-xl-7 text-center">
                    <?php echo $intro; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row <?php echo $column_class; ?> gx-lg-80 gy-30 gy-lg-40 gy-xl-50" data-scroll-fade-children>
            <?php if ($members) : foreach ($members as $member) : ?>
                    <div class="col">
                        <div class="team-member medium">
                            <div class="row <?php echo $count >= 6 ? 'flex-column text-center' : ''; ?> <?php echo $member['bio'] ? 'align-items-start' : 'align-items-center'; ?>">
                                <?php if ($member['photo']) : ?>
                                    <div class="<?php echo $count >= 6 ? 'col-6 col-sm-8 mb-20' : 'col-6 col-sm-4 mb-20 mb-sm-0'; ?>">
                                        <picture class="aspect-square rounded-circle bg-grey-2">
                                            <?php echo wp_get_attachment_image($member['photo'], 'square'); ?>
                                        </picture>
                                    </div>
                                <?php endif; ?>
                                <div class="<?php echo $count >= 6 ? 'col-12' : 'col-sm-8'; ?>">
                                    <?php if ($member['name']) : ?>
                                        <h3 class="mb-10"><?php echo $member['name']; ?></h3>
                                    <?php endif; ?>
                                    <?php if ($member['title']) : ?>
                                        <p class="meta text-dark-spruce"><?php echo $member['title']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($member['alternate_title']) : ?>
                                        <p class="mt-10 font-weight-semibold text-dark-spruce"><?php echo $member['alternate_title']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($member['link']) : $link = $member['link']; ?>
                                        <p class="mt-10">
                                            <?php echo $member['alternate_title']; ?>
                                            <a class="d-inline-flex align-items-center meta text-grey-3 no-underline" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" rel="<?php echo $link['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                                                <?php echo $link['title']; ?>
                                                <?php if ($link['target'] == '_blank') : ?>
                                                    <svg class="ms-10 icon">
                                                        <use xlink:href="#link" />
                                                    </svg>
                                                <?php endif; ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                    <?php echo $member['bio']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php endforeach;
                wp_reset_postdata();
            endif; ?>
        </div>
    </div>
</section>
