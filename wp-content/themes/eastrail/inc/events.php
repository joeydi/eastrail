<?php

new ET_Events();

class ET_Events
{
    function __construct()
    {
        add_action('init',          [$this, 'register_post_type_event']);
        add_action('init',          [$this, 'register_event_category']);

        add_filter('query_vars',    [$this, 'filter_query_vars']);
    }

    /**
     * Registers the `event` post type.
     */
    function register_post_type_event()
    {
        register_post_type('event', [
            'labels' => [
                'name'              => 'Events',
                'singular_name'     => 'Event',
                'edit_item'         => 'Edit Event',
            ],
            'supports' => [
                'title',
                'editor',
                'thumbnail',
            ],
            'hierarchical'          => false,
            'public'                => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_admin_bar'     => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'show_in_rest'          => true,
        ]);
    }

    function register_event_category()
    {
        register_taxonomy('event_category', ['event'], [
            'labels' => [
                'name'              => 'Categories',
                'singular_name'     => 'Category',
            ],
            'hierarchical'          => true,
            'public'                => false,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_nav_menus'     => true,
            'show_in_rest'          => true,
        ]);
    }

    function filter_query_vars($vars)
    {
        return array_merge($vars, [
            'start_date',
            'end_date',
        ]);
    }

    static function get_events($args = null)
    {
        $args = wp_parse_args($args, [
            'post_type' => 'event',
            'posts_per_page' => 8,
            'paged' => get_query_var('paged') ?: 1,
            'meta_query' => [],
            'meta_key' => 'start_date',
            'orderby' => 'meta_value',
            'order' => 'asc',
        ]);

        $end_date = get_query_var('end_date');
        if ($end_date) {
            $args['meta_query'][] = [
                'key'     => 'end_date',
                'value'   => $end_date,
                'type'    => 'numeric',
                'compare' => '<=',
            ];
        }

        $start_date = get_query_var('start_date');
        if ($start_date) {
            $args['meta_query'][] = [
                'key'     => 'start_date',
                'value'   => $start_date,
                'type'    => 'numeric',
                'compare' => '>=',
            ];
        }

        return new WP_Query($args);
    }

    static function get_upcoming_events($args = null)
    {
        $args = wp_parse_args($args, [
            'meta_query' => [
                [
                    'key'     => 'end_date',
                    'value'   => date('Ymd'),
                    'type'    => 'numeric',
                    'compare' => '>=',
                ],
            ],
        ]);

        return self::get_events($args);
    }

    static function get_past_events($args = null)
    {
        $args = wp_parse_args($args, [
            'meta_query' => [
                [
                    'key'     => 'end_date',
                    'value'   => date('Ymd'),
                    'type'    => 'numeric',
                    'compare' => '<',
                ],
            ],
        ]);

        return self::get_events($args);
    }

    static function is_event_upcoming($post = null)
    {
        $current_date = date('Y-m-d');
        $end_date = get_field('end_date', $post) ?: get_field('start_date', $post);
        return $end_date >= $current_date;
    }

    static function the_date()
    {
        $start_date = get_field('start_date');
        $start_time = strtotime($start_date);
        $end_date = get_field('end_date');
        $end_time = strtotime($end_date);

        if (date('Y', $start_time) != date('Y', $end_time)) {
            echo date('F j, Y', $start_time) . " &ndash; " . date('F j, Y', $end_time);
        } elseif (date('m', $start_time) != date('m', $end_time)) {
            echo date('F j', $start_time) . " &ndash; " . date('F j, Y', $end_time);
        } elseif (date('j', $start_time) != date('j', $end_time)) {
            echo date('F j', $start_time) . " &ndash; " . date('j, Y', $end_time);
        } else {
            echo date('F j, Y', $start_time);
        }
    }

    static function the_time()
    {
        $all_day = get_field('all_day');
        if ($all_day) {
            echo 'All Day';
            return;
        }

        $start_time = get_field('start_time');
        $end_time = get_field('end_time');
        echo "$start_time &ndash; $end_time";
    }
}
