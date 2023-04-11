<?php

new ET_GravityForms();

class ET_GravityForms
{
    function __construct()
    {
        add_filter("gform_field_content",               [$this, "filter_gform_field_content"], 10, 2);
        add_filter("gform_submit_button",               [$this, "filter_gform_submit_button"]);
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
        return str_replace('class=\'gform_button', 'class=\'btn btn-primary gform_button', $button);
    }
}
