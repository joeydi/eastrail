<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo ET::theme_url('/static/favicon/apple-touch-icon.png'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo ET::theme_url('/static/favicon/favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo ET::theme_url('/static/favicon/favicon-16x16.png'); ?>">
    <link rel="manifest" href="<?php echo ET::theme_url('/static/favicon/site.webmanifest'); ?>">
    <link rel="mask-icon" href="<?php echo ET::theme_url('/static/favicon/safari-pinned-tab.svg'); ?>" color="#00ae42">
    <link rel="shortcut icon" href="<?php echo ET::theme_url('/static/favicon/favicon.ico'); ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="<?php echo ET::theme_url('/static/favicon/browserconfig.xml'); ?>">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
    <?php echo get_field('head_tracking_codes', 'options'); ?>
</head>

<body <?php body_class(); ?>>
    <?php echo get_field('body_open_tracking_codes', 'options'); ?>

    <div style="display: none;">
        <?php include_once(ET::theme_path('static/icons/symbol/svg/sprite.symbol.svg')); ?>
    </div>

    <header>
        <?php get_template_part('partials/alert-bar'); ?>

        <div class="container">
            <div class="main-nav">
                <a class="logo" href="<?php echo site_url(); ?>" title="<?php bloginfo('sitename'); ?>">
                    <?php include(ET::theme_path('static/img/eastrail-logo.svg')); ?>
                </a>
                <button class="menu-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="visually-hidden">Show Menu</span>
                </button>
                <div class="menu-wrap">
                    <div class="menu-header">
                        <span class="visually-hidden">Menu</span>
                        <button class="menu-toggle">
                            <span class="bar"></span>
                            <span class="bar"></span>
                            <span class="bar"></span>
                            <span class="visually-hidden">Hide Menu</span>
                        </button>
                    </div>
                    <?php wp_nav_menu(['menu' => 'Header', 'container' => false, 'depth' => 3]); ?>
                    <?php wp_nav_menu(['menu' => 'Header Utility', 'menu_class' => 'utility', 'container' => false, 'depth' => 1]); ?>
                </div>
            </div>
        </div>
    </header>

    <main id="main">
