<?php

$label = get_sub_field('label');
$content = get_sub_field('content');
$faqs = get_sub_field('faqs');

?>
<section class="faqs section-margin" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
    <div class="container">
        <!-- <?php if ($content) : ?>
            <div class="row align-items-end mb-30 mb-md-40 mb-lg-50" data-scroll-fade-children>
                <div class="col-md-8">
                    <?php echo $content; ?>
                </div>
            </div>
        <?php endif; ?> -->

        <div class="row justify-content-center">
            <div class="col-md-10 col-xl-8" data-scroll-fade-children>
                <?php echo $content; ?>

                <?php if ($faqs) : ?>
                    <dl class="faqs">
                        <?php foreach ($faqs as $faq) : ?>
                            <dt>
                                <?php echo $faq['question']; ?>
                                <button type="button">
                                    <span class="visually-hidden">Show Answer</span>
                                </button>
                            </dt>
                            <dd>
                                <?php echo $faq['answer']; ?>
                            </dd>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
