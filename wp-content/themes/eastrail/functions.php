<?php

require 'inc/comments.php';
require 'inc/events.php';
require 'inc/gravityforms.php';
require 'inc/news.php';
require 'inc/simple_html_dom.php';
require 'inc/shortcodes.php';

new ET();

class ET
{
    private $version;

    public static $sm = '(min-width: 576px)';
    public static $md = '(min-width: 768px)';
    public static $lg = '(min-width: 992px)';
    public static $xl = '(min-width: 1200px)';

    function __construct()
    {
        $theme = wp_get_theme();
        $this->version = $theme->Version;

        add_theme_support('menus');
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');

        add_image_size('square', 1280, 1280, true);
        add_image_size('square-sm', 640, 640, true);
        add_image_size('block', 1920, 1540, true);
        add_image_size('block-sm', 960, 770, true);
        add_image_size('landscape', 1920, 1280, true);
        add_image_size('landscape-sm', 960, 640, true);
        add_image_size('widescreen', 1920, 1080, true);
        add_image_size('widescreen-sm', 960, 540, true);

        add_action('init',                                  [$this, 'action_add_post_type_support']);
        add_action('init',                                  [$this, 'action_acf_add_options_page']);
        add_action('init',                                  [$this, 'action_init_tablepress_overrides']);
        add_action('init',                                  [$this, 'action_register_acf_blocks']);
        add_action('wp_enqueue_scripts',                    [$this, 'action_enqueue_scripts']);
        add_action('wp_enqueue_scripts',                    [$this, 'action_enqueue_styles']);
        add_action('acf/input/admin_footer',                [$this, 'action_acf_admin_footer']);
        add_action('login_enqueue_scripts',                 [$this, 'action_login_enqueue_styles']);
        add_action('admin_init',                            [$this, 'add_editor_styles']);
        add_action('wp_ajax_nopriv_load_gravity_form',      [$this, 'action_load_gravity_form']);
        add_action('wp_ajax_load_gravity_form',             [$this, 'action_load_gravity_form']);

        remove_action('wp_head',                            'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts',                'print_emoji_detection_script');
        remove_action('wp_print_styles',                    'print_emoji_styles');
        remove_action('admin_print_styles',                 'print_emoji_styles');

        add_filter('big_image_size_threshold',              [$this, 'filter_big_image_size_threshold']);
        add_filter('wpseo_metabox_prio',                    [$this, 'filter_yoast_seo_metabox']);
        add_filter('wp_get_attachment_image_attributes',    [$this, 'filter_image_attributes']);
        add_filter('login_headertext',                      [$this, 'filter_login_headertext']);
        add_filter('login_headerurl',                       [$this, 'filter_login_headerurl']);
        add_filter('the_permalink',                         [$this, 'filter_the_permalink']);
        add_filter('get_the_categories',                    [$this, 'filter_get_the_categories']);
        add_filter('get_the_archive_title',                 [$this, 'filter_get_the_archive_title']);
        add_filter('body_class',                            [$this, 'filter_body_class']);
        add_filter('mce_buttons_2',                         [$this, 'filter_mce_buttons_2']);
        add_filter('tiny_mce_before_init',                  [$this, 'filter_tiny_mce_before_init']);
        add_filter('wp_nav_menu_objects',                   [$this, 'filter_wp_nav_menu_objects'], 10, 2);
        add_filter('wp_nav_menu_items',                     [$this, 'filter_wp_nav_menu_items'], 10, 2);
    }

    function add_editor_styles()
    {
        add_editor_style('static/css/editor.css');
    }

    function action_add_post_type_support()
    {
        add_post_type_support('page', 'excerpt');
    }

    function action_acf_add_options_page()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => __('Theme Settings'),
                'menu_title'    => __('Theme Settings'),
                'menu_slug'     => 'theme-settings',
                'parent_slug'   => 'options-general.php',
                'capability'    => 'manage_options',
            ]);
        }
    }

    function action_init_tablepress_overrides()
    {
        if (class_exists('TablePress_Frontend_Controller')) {
            require 'inc/tablepress.php';
        }
    }

    function action_register_acf_blocks()
    {
        register_block_type(__DIR__ . '/blocks/faqs');
    }

    function action_enqueue_scripts()
    {
        // Header
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js');

        // Footer
        wp_enqueue_script('main', get_stylesheet_directory_uri() . '/static/js/main.min.js', ['jquery'], $this->version, true);

        $data = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'template_directory_url' => get_stylesheet_directory_uri(),
        ];

        wp_localize_script('main', 'ET', $data);
    }

    function action_enqueue_styles()
    {
        wp_dequeue_style('wp-block-library');
        wp_enqueue_style('main', get_stylesheet_directory_uri() . '/static/css/main.css', false, $this->version);
        wp_enqueue_style('gfonts', 'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,300;0,400;0,700;1,400&display=swap', false, $this->version);
    }

    function action_acf_admin_footer()
    {
?>
        <script type="text/javascript">
            (function($) {
                acf.add_filter('color_picker_args', function(args, $field) {
                    args.palettes = [
                        '#ffffff',
                        '#f6f6f8',
                        '#000000',
                        '#00ccd6',
                        '#0ba8ff',
                        '#2e3fb3',
                        '#4f40fb',
                        '#2e0379',
                    ];

                    return args;
                });
            })(jQuery);
        </script>
<?php
    }

    function action_login_enqueue_styles()
    {
        wp_enqueue_style('customlogin', get_stylesheet_directory_uri() . '/static/css/login.css', false, $this->version);
    }

    function action_download()
    {
        $attachment_id = $_REQUEST['attachment_id'];

        if (empty($attachment_id)) {
            echo json_encode(array(
                'error' => 'You must specify an Attachment ID'
            ));
            exit;
        }

        $attachment = get_attached_file($attachment_id);

        if (empty($attachment)) {
            echo json_encode(array(
                'error' => 'No Attachment found for the specified ID'
            ));
            exit;
        }

        if (!file_exists($attachment)) {
            echo json_encode(array(
                'error' => 'Attachment file missing'
            ));
            exit;
        }

        // Process download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($attachment) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($attachment));
        readfile($attachment);
        exit;
    }

    function action_load_gravity_form()
    {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        gravity_form($id, false, false, false, '', true);
        die();
    }

    function filter_big_image_size_threshold()
    {
        return 2560;
    }

    function filter_yoast_seo_metabox()
    {
        return 'low';
    }

    /**
     * Change src and srcset to data-src and data-srcset, and add class 'lazyload'
     * @param $attributes
     * @return mixed
     */
    function filter_image_attributes($attributes)
    {
        if (is_admin()) {
            return $attributes;
        }

        if (isset($attributes['src'])) {
            $attributes['data-src'] = $attributes['src'];
        }

        if (isset($attributes['srcset'])) {
            $attributes['data-srcset'] = $attributes['srcset'];
            $attributes['srcset'] = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        }

        $attributes['class'] .= ' lazyload';

        unset($attributes['sizes']);
        $attributes['data-sizes'] = 'auto';

        $attributes['data-object-fit'] = 'cover';

        return $attributes;
    }

    function filter_login_headertext()
    {
        return get_bloginfo('name');
    }

    function filter_login_headerurl()
    {
        return site_url('/');
    }

    function filter_the_permalink($url)
    {
        $permalink = get_field('_links_to');

        return $permalink ? $permalink : $url;
    }

    function filter_get_the_categories($categories)
    {
        $categories = array_filter($categories, function ($category) {
            return $category->name !== 'Uncategorized';
        });

        return $categories;
    }

    function filter_get_the_archive_title($title)
    {
        if (is_category()) {
            $title = single_cat_title('', false);
        } elseif (is_tag()) {
            $title = single_cat_title('', false);
        } elseif (is_author()) {
            $title = sprintf('<span class="vcard">%s</span>', get_the_author());
        } elseif (is_search()) {
            $title = sprintf('<span class="eyebrow text-white">Results for</span><br /> %s', get_search_query());
        }

        return $title;
    }

    function filter_body_class($classes)
    {
        global $post;

        if (isset($post) && is_singular()) {
            $classes[] = $post->post_type . '-' . $post->post_name;
        }

        return $classes;
    }

    function filter_mce_buttons_2($buttons)
    {
        array_unshift($buttons, 'styleselect');
        return $buttons;
    }

    function filter_tiny_mce_before_init($init)
    {
        $style_formats = [
            [
                'title' => 'Lead',
                'selector' => 'p',
                'classes' => 'lead',
                'wrapper' => false,
            ],
            [
                'title' => 'Eyebrow',
                'selector' => 'p',
                'classes' => 'eyebrow',
                'wrapper' => false,
            ],
            [
                'title' => 'Meta',
                'selector' => 'p',
                'classes' => 'meta',
                'wrapper' => false,
            ],
            [
                'title' => 'Small',
                'selector' => 'p, ul, ol',
                'classes' => 'small',
                'wrapper' => false,
            ],
            [
                'title' => 'Medium',
                'selector' => 'p, ul, ol',
                'classes' => 'medium',
                'wrapper' => false,
            ],
            [
                'title' => 'Large',
                'selector' => 'p, ul, ol',
                'classes' => 'large',
                'wrapper' => false,
            ],
            [
                'title' => 'Font Weight Medium',
                'selector' => '*',
                'classes' => 'font-weight-medium',
                'wrapper' => false,
            ],
            [
                'title' => 'Font Weight Extra Bold',
                'selector' => '*',
                'classes' => 'font-weight-extrabold',
                'wrapper' => false,
            ],
            [
                'title' => 'Button - Dark Green',
                'selector' => 'a',
                'classes' => 'btn btn-primary',
                'wrapper' => false,
            ],
            [
                'title' => 'Button - Dark Green Outline',
                'selector' => 'a',
                'classes' => 'btn btn-outline-primary',
                'wrapper' => false,
            ],
            [
                'title' => 'Button - Light Green',
                'selector' => 'a',
                'classes' => 'btn btn-secondary',
                'wrapper' => false,
            ],
            [
                'title' => 'Button - Light Green Outline',
                'selector' => 'a',
                'classes' => 'btn btn-outline-secondary',
                'wrapper' => false,
            ],
            [
                'title' => 'Icon Link',
                'selector' => 'a',
                'classes' => 'icon-link',
                'wrapper' => false,
            ],
            [
                'title' => 'Popup Link',
                'selector' => 'a',
                'classes' => 'popup',
                'wrapper' => false,
            ],
            [
                'title' => 'Numbered List',
                'selector' => 'ul, ol',
                'classes' => 'numbered',
                'wrapper' => false,
            ],
            [
                'title' => 'Ruled List',
                'selector' => 'ul, ol',
                'classes' => 'ruled',
                'wrapper' => false,
            ],
            [
                'title' => 'Checkmark List',
                'selector' => 'ul, ol',
                'classes' => 'checkmarks',
                'wrapper' => false,
            ],
            [
                'title' => 'Warning List',
                'selector' => 'ul, ol',
                'classes' => 'warning',
                'wrapper' => false,
            ],
            [
                'title' => 'Icons List',
                'selector' => 'ul, ol',
                'classes' => 'icons',
                'wrapper' => false,
            ],
            [
                'title' => 'Basic List',
                'selector' => 'ul, ol',
                'classes' => 'basic',
                'wrapper' => false,
            ],
            [
                'title' => 'Two Columns',
                'selector' => 'p, ul, ol',
                'classes' => 'two-columns',
                'wrapper' => false,
            ],
            [
                'title' => 'Three Columns',
                'selector' => 'p, ul, ol',
                'classes' => 'three-columns',
                'wrapper' => false,
            ],
        ];

        $init['style_formats'] = json_encode($style_formats);

        $custom_colours = [
            'ffffff', 'White',
            'f6f6f8', 'Light Grey',
            '000000', 'Black',
            '00ccd6', 'Teal',
            '0ba8ff', 'Light Blue',
            '2e3fb3', 'Blue',
            '4f40fb', 'Purple',
            '2e0379', 'Dark Purple',
        ];

        $init['textcolor_map'] = json_encode($custom_colours);
        $init['textcolor_cols'] = 8;

        return $init;
    }

    function filter_wp_nav_menu_objects($items, $args)
    {
        if ($args->menu === 'Social') {
            foreach ($items as &$item) {
                $icon = get_field('icon', $item);
                if ($icon) {
                    $mime_type = get_post_mime_type($icon);

                    if ($mime_type === 'image/svg+xml') {
                        $file = get_attached_file($icon);
                        ob_start();
                        include $file;
                        $image = ob_get_clean();
                    } else {
                        $image = wp_get_attachment_image($icon);
                    }

                    $item->title = sprintf('%s<span class="visually-hidden">%s</span>', $image, $item->title);
                }
            }
        }

        return $items;
    }

    function filter_wp_nav_menu_items($items, $args)
    {
        $html = str_get_html($items);

        if ($args->menu === 'Header') {
            $button = '<button class="sub-menu-toggle"><svg><title>Toggle Submenu</title><use xlink:href="#chevron-down" /></svg></button>';

            $nested_parents = $html->find('li li.menu-item-has-children');
            foreach ($nested_parents as $parent) {
                $parent->innertext .= $button;
            }

            $parents = $html->find('li.menu-item-has-children');
            foreach ($parents as $parent) {
                $parent->innertext .= $button;
            }
        }

        if ($args->menu === 'Header Utility') {
            $parents = $html->find('li');

            foreach ($parents as $parent) {
                if ($parent->hasClass('btn')) {
                    $anchor = $parent->find('a', 0);
                    $anchor->class = $parent->class;
                    $parent->class = '';
                }
            }
        }

        return $html;
    }

    static function theme_url($path)
    {
        $path = (0 === strpos($path, '/')) ? $path : '/' . $path;
        return get_stylesheet_directory_uri() . $path;
    }

    static function theme_path($path)
    {
        $path = (0 === strpos($path, '/')) ? $path : '/' . $path;
        return get_template_directory() . $path;
    }

    static function remote_get($url)
    {
        if ($cached = get_transient($url)) {
            return $cached;
        }

        $response = wp_remote_get($url);

        if (!is_wp_error($response)) {
            set_transient($url, $response['body'], 60 * 60 * 24);
            return $response['body'];
        }
    }

    static function truncate_string($string, $limit = 250, $break = '.', $pad = '...')
    {
        // return with no change if string is shorter than $limit
        if (strlen($string) <= $limit) {
            return $string;
        }

        // is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        }

        return $string;
    }

    static function widont($str = '')
    {
        $str = rtrim($str);
        $space = strrpos($str, ' ');
        if ($space !== false) {
            $str = substr($str, 0, $space) . '&nbsp;' . substr($str, $space + 1);
        }
        return $str;
    }

    static function format_url($url)
    {
        $parts = parse_url($url);

        if (!empty($parts['host'])) {
            return str_replace('www.', '', $parts['host']);
        }

        return $url;
    }

    static function format_filesize($bytes, $decimals = 1)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    static function get_post_type()
    {
        $post_type_names = [
            'post' => 'news',
            'provider' => 'doctor',
        ];

        $post_type = get_post_type();

        return isset($post_type_names[$post_type]) ? $post_type_names[$post_type] : $post_type;
    }

    static function get_the_term_names($post, $taxonomy)
    {
        $terms = get_the_terms($post, $taxonomy);

        if (is_wp_error($terms)) {
            return $terms;
        }

        if (is_array($terms)) {
            $names = wp_list_pluck($terms, 'name');
            return implode(', ', $names);
        }

        return '—';
    }

    static function get_the_category_name()
    {
        $categories = get_the_category();

        // If there are multiple categories, return the first child category name
        if (count($categories) > 1) {
            $children = array_values(array_filter($categories, function ($cat) {
                return !!$cat->category_parent;
            }));

            if (!empty($children)) {
                return $children[0]->name;
            }

            return $categories[0]->name;
        }

        // If there is one category, return that
        if (count($categories) == 1) {
            return $categories[0]->name;
        }

        // If there are no categories, return default string
        return 'Uncategorized';
    }

    static function the_category_name()
    {
        echo self::get_the_category_name();
    }

    static function the_target()
    {
        $permalink = get_field('_links_to');

        echo $permalink ? '_blank' : '';
    }

    static function the_rel()
    {
        $permalink = get_field('permalink');
        echo $permalink ? 'noopener noreferrer' : '';
    }

    static function image_aspect($attachment_id, $size)
    {
        // Default dimensions
        $width = 1920;
        $height = 1280;

        $metadata = wp_get_attachment_metadata($attachment_id, true);
        $mime_type = get_post_mime_type($attachment_id);

        if ($mime_type == 'image/svg+xml') {
            $width = $metadata["width"];
            $height = $metadata["height"];
        } elseif (!empty($metadata['sizes'][$size])) {
            $width = $metadata['sizes'][$size]["width"];
            $height = $metadata['sizes'][$size]["height"];
        } elseif (!empty($metadata['width']) && !empty($metadata['height'])) {
            $width = $metadata["width"];
            $height = $metadata["height"];
        } elseif ($src = wp_get_attachment_image_src($attachment_id, $size)) {
            $width = $src[1];
            $height = $src[2];
        }

        $ratio = $height / $width;
        return number_format($ratio * 100, 2) . '%';
    }

    static function section_classes()
    {
        $background = get_sub_field('background');

        $classes = [
            $background,
            $background ? 'section-padding' : 'section-margin'
        ];

        echo implode(' ', $classes);
    }
}
