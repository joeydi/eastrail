<?php

$label = get_sub_field('label');
$members = get_sub_field('members');

?>

<section class="team-members <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row row-cols-1 row-cols-lg-2 gx-lg-80 gy-60 gy-lg-80 gy-xl-100 gy-xxl-120" data-scroll-fade-children>
            <?php if ($members) : foreach ($members as $member) : ?>
                    <div class="col">
                        <div class="team-member medium">
                            <div class="row <?php echo $member['bio'] ? 'align-items-start' : 'align-items-center'; ?>">
                                <div class="col-6 col-sm-4">
                                    <picture class="aspect-square rounded-circle bg-grey-2 mb-30 mb-sm-0">
                                        <?php echo wp_get_attachment_image($member['photo'], 'square'); ?>
                                    </picture>
                                </div>
                                <div class="col-sm-8">
                                    <?php if ($member['name']) : ?>
                                        <h3 class="mb-10"><?php echo $member['name']; ?></h3>
                                    <?php endif; ?>
                                    <?php if ($member['title']) : ?>
                                        <p class="mb-10 meta text-spruce"><?php echo $member['title']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($member['alternate_title']) : ?>
                                        <p class="font-weight-semibold text-dark-spruce"><?php echo $member['alternate_title']; ?></p>
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
