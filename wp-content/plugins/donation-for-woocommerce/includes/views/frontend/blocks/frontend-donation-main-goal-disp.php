<?php
$flag_for_widget_progress = false;

if ( 'widget' === $_type && 'enabled' === $progressOnWidget ) {
	$flag_for_widget_progress = true;
} 

if ( 'widget' !== $_type ) {
	$flag_for_widget_progress = true;
}

if ( 'enabled' === $goalDisp && $flag_for_widget_progress ) {
	require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-goal-disp.php' ;
}
