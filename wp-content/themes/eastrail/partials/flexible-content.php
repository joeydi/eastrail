<?php

if (have_rows('flexible_content')) {
    while (have_rows('flexible_content')) {
        the_row();
        get_template_part('partials/layout-' . get_row_layout());
    }
}
