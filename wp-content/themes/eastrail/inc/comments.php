<?php

new ET_Comments();

class ET_Comments
{
    function __construct()
    {
        add_action('init', [$this, 'remove_comment_support'], 100);
        add_action('admin_menu', [$this, 'remove_comments_menu_page']);
        add_action('wp_before_admin_bar_render', [$this, 'remove_admin_bar_comments']);
    }

    function remove_comment_support()
    {
        remove_post_type_support('post', 'comments');
        remove_post_type_support('page', 'comments');
    }

    function remove_comments_menu_page()
    {
        remove_menu_page('edit-comments.php');
    }

    function remove_admin_bar_comments()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }
}
