<?php

new ET_Shortcodes();

class ET_Shortcodes
{
    function __construct()
    {
        add_action('init', [$this, 'register_shortcodes']);
    }

    function register_shortcodes()
    {
        add_shortcode('icon', [$this, 'icon_callback']);
        add_shortcode('step', [$this, 'step_callback']);
    }

    function icon_callback($atts = [], $content = '')
    {
        $content = preg_replace('/^<\/p>/', '', $content);
        $content = preg_replace('/<p>$/', '', $content);

        $format = '<div class="icon-block"><img src="%s" class="icon" /><div class="content">%s</div></div>';

        return sprintf($format, $atts['src'], $content);
    }

    function step_callback($atts = [], $content = '')
    {
        $content = preg_replace('/^<\/p>/', '', $content);
        $content = preg_replace('/<p>$/', '', $content);

        $format = '<div class="step-block"><div class="number">%s</div><div class="content">%s</div></div>';

        return sprintf($format, $atts['number'], $content);
    }
}
