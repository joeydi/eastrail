<?php

$label = get_sub_field('label');
$media_url = get_sub_field('media_url');
$content = get_sub_field('content');
$justify_content = get_sub_field('justify_content');

?>
<section class="media-embed <?php echo ET::section_classes(); ?>" id="<?php echo sanitize_title($label); ?>" aria-label="<?php echo $label; ?>">
  <div class="container">
    <?php if ($content) : ?>
      <div class="row mb-40 mb-lg-60 <?php echo $justify_content; ?>">
        <div class="col-lg-10 col-xl-8" data-scroll-fade-children>
          <?php echo $content; ?>
        </div>
      </div>
    <?php endif; ?>

    <div data-scroll-fade>
      <?php echo ET::video_embed($media_url); ?>
    </div>
  </div>
</section>
