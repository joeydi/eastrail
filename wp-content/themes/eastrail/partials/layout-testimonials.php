<?php

$label = get_sub_field('label');
$heading = get_sub_field('heading');
$cta = get_sub_field('cta');
$testimonials = get_sub_field('testimonials');
$background = get_sub_field('background');

?>
<section class="testimonials text-center <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-10" data-scroll-fade-children>
                <h2 class="mb-lg-50 mb-xl-70"><?php echo $heading; ?></h2>

                <?php if ($testimonials) : ?>
                    <div class="swiper testimonials-slider">
                        <div class="swiper-wrapper">
                            <?php foreach ($testimonials as $testimonial) : ?>
                                <div class="swiper-slide">
                                    <blockquote class="mb-lg-50 mb-xl-70">
                                        &ldquo;<?php echo $testimonial['quote']; ?>&rdquo;
                                    </blockquote>
                                    <cite>
                                        &mdash; <?php echo $testimonial['name']; ?><?php echo $testimonial['title'] ? ',' : ''; ?>
                                        <?php echo $testimonial['title']; ?>
                                    </cite>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($testimonials) > 1) : ?>
                            <div class="swiper-button-prev">
                                <svg class="icon">
                                    <use xlink:href="#arrow-left" />
                                </svg>
                            </div>
                            <div class="swiper-button-next">
                                <svg class="icon">
                                    <use xlink:href="#arrow-right" />
                                </svg>
                            </div>
                            <div class="swiper-pagination my-10"></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($cta) : ?>
                    <div class="mt-40 mt-lg-60">
                        <a class="btn btn-outline-primary" href="<?php echo $cta['url']; ?>" target="<?php echo $cta['target']; ?>" rel="<?php echo $cta['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                            <?php echo $cta['title']; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
