<?php if (get_the_content()) : ?>
    <section class="section-margin">
        <div class="container">
            <div class="row">
                <div class="col-md-10 offset-md-1 col-xl-8 offset-xl-2 col-xxl-7" data-scroll-fade>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
