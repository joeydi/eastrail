<?php

new ET_Woo();

class ET_Woo
{
  function __construct()
  {
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

    add_action('woocommerce_before_main_content',   [$this, 'action_woocommerce_before_main_content']);
    add_action('woocommerce_after_main_content',    [$this, 'action_woocommerce_after_main_content']);
    add_action('template_redirect',                 [$this, 'action_nocache_headers']);
    add_action('wp_ajax_get_donation_nonce',        [$this, 'action_get_donation_nonce']);
    add_action('wp_ajax_nopriv_get_donation_nonce', [$this, 'action_get_donation_nonce']);
  }

  function action_woocommerce_before_main_content()
  {
    echo '<section class="section-margin"><div class="container"><div class="row justify-content-center"><div class="col-md-10 col-xl-8" data-scroll-fade>';
  }

  function action_woocommerce_after_main_content()
  {
    echo '</div></div></div></section>';
  }

  function action_nocache_headers()
  {
    if (is_page('donate')) {
      if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
      }

      if (function_exists('wc_nocache_headers')) {
        wc_nocache_headers();
      } else {
        nocache_headers();
      }
    }
  }

  function action_get_donation_nonce()
  {
    // Make absolutely sure this response is never cached:
    if (! defined('DONOTCACHEPAGE')) {
      define('DONOTCACHEPAGE', true);
    }

    if (function_exists('nocache_headers')) {
      nocache_headers();
    }

    // Generate a fresh nonce for the donation action:
    $nonce = wp_create_nonce('_wcdnonce');

    wp_send_json_success([
      'nonce' => $nonce,
    ]);
  }
}
