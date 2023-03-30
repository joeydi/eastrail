<?php

/**
 * FAQ Block Template.
 */

// Support custom "anchor" values.

$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
    $anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.

$class_name = 'faqs-block';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

// Load values and assign defaults.

$faqs = get_field('faqs'); ?>

<section class="faqs">
    <div class="container-full">
        <div class="row">
            <div class="col-md-12" data-scroll-fade-children>
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
                <?php else : ?>
                    <dl class="faqs">
                        <dt><?php echo 'FAQ Headline will appear here'; ?></dt>
                        <dd><?php echo 'FAQ Answer will appear here'; ?></dd>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
