<?php

new ET_GravityForms();

class ET_GravityForms
{
    function __construct()
    {
        add_action('init',                              [$this, 'register_shortcodes'], 9999);
        add_action('wp_enqueue_scripts',                [$this, 'action_enqueue_scripts']);
        add_action('wp_ajax_nopriv_load_gravity_form',  [$this, 'action_load_gravity_form']);
        add_action('wp_ajax_load_gravity_form',         [$this, 'action_load_gravity_form']);

        add_filter("gform_field_content",               [$this, "filter_gform_field_content"], 10, 2);
        add_filter("gform_submit_button",               [$this, "filter_gform_submit_button"]);
        add_filter("gform_entry_post_save",             [$this, "filter_gform_entry_post_save"]);
    }

    function register_shortcodes()
    {
        remove_shortcode('gravityform');
        remove_shortcode('gravityforms');

        add_shortcode('gravityform', [$this, 'gravityform']);
        add_shortcode('gravityforms', [$this, 'gravityform']);
    }

    function gravityform($atts = [])
    {
        return sprintf('<div class="gform" data-id="%s"></div>', $atts['id']);
    }

    function action_enqueue_scripts()
    {
        if (!class_exists('GFAPI')) {
            return;
        }

        // Load all Gravity Forms scripts
        $forms = GFAPI::get_forms();
        foreach ($forms as $form) {
            gravity_form_enqueue_scripts($form['id'], true);
        }
    }

    function action_load_gravity_form()
    {
        if (function_exists('gravity_form')) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            gravity_form($id, false, false, false, '', true);
            die();
        } else {
            wp_send_json_error('Gravity Forms plugin is not installed.');
        }
    }

    /**
     * Gravity Forms Bootstrap Styles
     *
     * Applies bootstrap classes to various common field types.
     * Requires Bootstrap to be in use by the theme.
     *
     * Using this function allows use of Gravity Forms default CSS
     * in conjuction with Bootstrap (benefit for fields types such as Address).
     *
     * @see  gform_field_content
     * @link http://www.gravityhelp.com/documentation/page/Gform_field_content
     *
     * @return string Modified field content
     */
    function filter_gform_field_content($content, $field)
    {
        if ($field["type"] != 'hidden' && $field["type"] != 'list' && $field["type"] != 'multiselect' && $field["type"] != 'checkbox' && $field["type"] != 'fileupload' && $field["type"] != 'date' && $field["type"] != 'html' && $field["type"] != 'address') {
            $content = str_replace('class=\'small', 'class=\'form-control small', $content);
            $content = str_replace('class=\'medium', 'class=\'form-control medium', $content);
            $content = str_replace('class=\'large', 'class=\'form-control large', $content);
        }

        if ($field["type"] == 'name' || $field["type"] == 'address') {
            $content = str_replace('<input ', '<input class=\'form-control\' ', $content);
        }

        if ($field["type"] == 'textarea') {
            $content = str_replace('class=\'textarea', 'class=\'form-control textarea', $content);
        }

        if ($field["type"] == 'checkbox') {
            $content = str_replace('li class=\'', 'li class=\'checkbox ', $content);
            $content = str_replace('<input ', '<input style=\'margin-left:1px;\' ', $content);
        }

        if ($field["type"] == 'radio') {
            $content = str_replace('li class=\'', 'li class=\'radio ', $content);
            $content = str_replace('<input ', '<input style=\'margin-left:1px;\' ', $content);
        }

        return $content;
    }

    function filter_gform_submit_button($button)
    {
        return str_replace('class=\'gform_button', 'class=\'btn btn-outline-primary gform_button', $button);
    }

    function filter_gform_entry_post_save($entry)
    {
        if (!class_exists('GFAPI')) {
            return;
        }

        // Replace Source URL for forms loaded with AJAX
        if (stripos($entry['source_url'], 'admin-ajax.php') !== false && !empty($_SERVER['HTTP_REFERER'])) {
            $entry['source_url'] = $_SERVER['HTTP_REFERER'];
            GFAPI::update_entry_property($entry['id'], 'source_url', $entry['source_url']);
        }

        return $entry;
    }
}
