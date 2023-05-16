<?php

new ET_Woo();

class ET_Woo
{
    function __construct()
    {
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);

        add_action('woocommerce_before_main_content',   [$this, 'action_woocommerce_before_main_content']);
        add_action('woocommerce_after_main_content',    [$this, 'action_woocommerce_after_main_content']);
    }

    function action_woocommerce_before_main_content()
    {
        echo '<section class="section-margin"><div class="container"><div class="row justify-content-center"><div class="col-md-10 col-xl-8" data-scroll-fade>';
    }

    function action_woocommerce_after_main_content()
    {
        echo '</div></div></div></section>';
    }
}
