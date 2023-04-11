<?php if (get_the_content()) : ?>
    <section class="post-content section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-xl-8" data-scroll-fade>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
