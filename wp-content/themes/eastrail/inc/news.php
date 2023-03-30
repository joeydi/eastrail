<?php

new ET_News();

class ET_News
{
    static function get($args = null)
    {
        $args = wp_parse_args($args, [
            'post_type' => 'post',
            'posts_per_page' => 9,
            'paged' => get_query_var('paged') ?: 1,
        ]);

        // Exclude featured posts from main query
        if ($featured_news = get_field('featured_news')) {
            $featured_news_ids = $featured_news ? wp_list_pluck($featured_news, 'ID') : [];
            $args['post__not_in'] = $featured_news_ids;
        }

        if ($category_name = get_query_var('category_name')) {
            $args['category_name'] = $category_name;
        }

        return new WP_Query($args);
    }

    static function get_featured_posts($limit = 3)
    {
        $featured_posts = get_field('featured_posts') ?: [];
        $featured_ids = wp_list_pluck($featured_posts, 'ID');
        $count = count($featured_posts);

        if ($count < $limit) {
            $recent = new WP_Query([
                'post_type' => ['post', 'event'],
                'posts_per_page' => $limit - $count,
                'post__not_in' => $featured_ids,
            ]);
            $featured_posts = array_merge($featured_posts, $recent->posts);
        }

        return $featured_posts;
    }
}
