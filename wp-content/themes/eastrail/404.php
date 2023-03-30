<?php

$page_not_found_content = get_field('page_not_found_content', 'options');

get_header();

?>

<section class="page-header"></section>

<section class="content section-margin" data-scroll-fade>
    <div class="container">
        <div class="row">
            <div class="col-md-10 offset-md-1 col-xl-8 offset-xl-2 col-xxl-7">
                <?php if ($page_not_found_content) : ?>
                    <?php echo $page_not_found_content; ?>
                <?php else : ?>
                    <h1 class="h2">Page Not Found</h1>
                    <p>Sorry, but the requested page could not be found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
