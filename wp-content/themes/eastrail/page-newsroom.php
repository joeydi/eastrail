<?php

// Template Name: Newsroom

$news = ET_News::get();
$featured_news = get_field('featured_news');
$featured_news_cta = get_field('featured_news_cta');
$paged = get_query_var('paged') ?: 1;
$categories = get_terms([
    'taxonomy' => 'category',
    'exclude' => 1,
]);
$selected_category = get_query_var('category_name');

get_header();

?>

<?php get_template_part('partials/page-header'); ?>

<?php if ($featured_news && $paged == 1 && !$selected_category) : ?>
    <section class="section-margin bg-white">
        <div class="container">
            <div class="d-sm-flex align-items-end justify-content-between mb-40 mb-lg-60" data-scroll-fade-children>
                <h2 class="mb-sm-0">Featured news</h2>

                <?php if ($featured_news_cta) : $link = $featured_news_cta; ?>
                    <a class="btn btn-outline-primary" href="<?php echo $link['url']; ?>" target="<?php echo $link['target']; ?>" rel="<?php echo $link['target'] == '_blank' ? 'noopener noreferrer' : ''; ?>">
                        <?php echo $link['title']; ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="row row-cols-1 row-cols-md-2 g-30" data-scroll-fade-children>
                <?php foreach ($featured_news as $post) : setup_postdata($post); ?>
                    <div class="col">
                        <a class="featured-post-excerpt" href="<?php the_permalink() ?>" target="<?php ET::the_target() ?>" rel="<?php ET::the_rel() ?>">
                            <picture class="aspect-block">
                                <?php the_post_thumbnail('block'); ?>
                            </picture>
                            <h3 class="h4"><?php the_title(); ?></h3>
                        </a>
                    </div>
                <?php endforeach;
                wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="post-grid section-margin">
    <div class="container">
        <form action="<?php the_permalink(); ?>" class="post-filter mb-40 mb-lg-60" data-scroll-fade>
            <div class="row">
                <div class="col-md-4">
                    <label for="category-filter" class="visually-hidden">Filter news</label>
                    <select name="category_name" id="category-filter" class="form-control">
                        <option value="">All categories</option>
                        <?php if ($categories) : foreach ($categories as $term) : ?>
                                <?php $selected = $selected_category == $term->slug ? 'selected' : ''; ?>
                                <option value="<?php echo $term->slug; ?>" <?php echo $selected; ?>>
                                    <?php echo $term->name; ?>
                                </option>
                        <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-outline-primary">Filter</button>
                </div>
            </div>
        </form>

        <?php if ($news->have_posts()) : ?>
            <div class="posts">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-30" data-scroll-fade-children>
                    <?php while ($news->have_posts()) : $news->the_post(); ?>
                        <div class="col">
                            <a class="post-excerpt" href="<?php the_permalink() ?>" target="<?php ET::the_target() ?>" rel="<?php ET::the_rel() ?>">
                                <picture class="aspect-landscape">
                                    <?php the_post_thumbnail('landscape'); ?>
                                </picture>
                                <div class="content p-20 p-lg-40 pt-lg-30">
                                    <p class="meta text-dark-spruce mb-10"><?php echo ET::the_category_name(); ?></p>
                                    <h3 class="text-dark-evergreen my-10"><?php the_title(); ?></h3>
                                    <div class="medium"><?php the_excerpt(); ?></div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
                <div class="pagination text-center mt-60 mt-lg-80" data-scroll-fade>
                    <?php wp_pagenavi([
                        'query' => $news,
                        'options' => [
                            'prev_text' => '<svg class="icon"><use xlink:href="#arrow-left" /></svg><span class="visually-hidden">Previous</span>',
                            'next_text' => '<span class="visually-hidden">Next</span> <svg class="icon"><use xlink:href="#arrow-right" /></svg>',
                        ]
                    ]); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="posts" data-scroll-fade>
                <h3>Sorry, no posts were found.</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_template_part('partials/flexible-content'); ?>

<?php get_footer(); ?>
