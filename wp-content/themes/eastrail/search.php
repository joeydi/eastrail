<?php

$post_types = [
    'News' => 'post',
    'Page' => 'page',
    'Event' => 'event',
    'Location' => 'location',
    'Provider' => 'provider',
    'Service' => 'service',
];
$selected_post_type = get_query_var('post_type');

get_header();

?>

<section class="page-header"></section>

<section class="post-grid section-margin">
    <div class="container">
        <form action="/" class="search mb-40 mb-lg-60 mb-xl-80">
            <div>
                <svg class="icon">
                    <use xlink:href="#search" />
                </svg>
                <input class="form-control" type="text" name="s" value="<?php echo get_query_var('s'); ?>" />
                <input type="submit" class="visually-hidden" />
            </div>
        </form>

        <?php if (have_posts()) : ?>
            <h2 class="h4 font-weight-regular text-grey-3 mb-30 mb-lg-40" data-scroll-fade><?php echo $wp_query->found_posts; ?> results found</h2>

            <?php if ($wp_query->found_posts > 6) : ?>
                <form action="<?php site_url(); ?>" class="post-filter mb-40 mb-lg-60" data-scroll-fade>
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-20 mb-md-0">
                            <label for="post_type" class="visually-hidden">Filter search results</label>
                            <select name=" post_type" id="post_type" class="form-control">
                                <option value="">Filter search results</option>
                                <?php if ($post_types) : foreach ($post_types as $name => $type) : ?>
                                        <?php $selected = $selected_post_type == $type ? 'selected' : ''; ?>
                                        <option value="<?php echo $type; ?>" <?php echo $selected; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-primary">Filter</button>
                            <input type="hidden" name="s" value="<?php echo get_query_var('s'); ?>" />
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <div class="posts">
                <div class="row">
                    <div class="col-md-9 col-lg-8 col-xl-7" data-scroll-fade-children>
                        <?php while (have_posts()) : the_post(); ?>
                            <div class="col">
                                <a href="<?php the_permalink() ?>" class="search-excerpt mb-40 mb-lg-60">
                                    <p class="meta mb-10"><?php echo ET::get_post_type(); ?></p>
                                    <h2 class="h4 text-maroon mt-0 mb-15"><?php the_title(); ?></h2>
                                    <?php the_excerpt(); ?>
                                </a>
                            </div>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>
                    <div class="pagination mt-60 mt-lg-80" data-scroll-fade>
                        <?php wp_pagenavi([
                            'options' => [
                                'prev_text' => '<svg class="icon"><use xlink:href="#arrow-left" /></svg><span class="visually-hidden">Previous</span>',
                                'next_text' => '<span class="visually-hidden">Next</span> <svg class="icon"><use xlink:href="#arrow-right" /></svg>',
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="posts" data-scroll-fade>
                <h3>Sorry, no posts were found.</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
