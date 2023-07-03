<?php
$forms  = class_exists('GFAPI') ? GFAPI::get_forms() : false;

$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

include 'header.php';
?>

<?php
if (!class_exists('woocommerce') && $WC_LICENSE):
	?>

    <section class="lgl-section" style="margin-top: 17px;">
        <p class="lgl-msg lgl-error-msg lgl-gf-error-msg">
            Your license includes an integration with the WooCommerce plugin, which seems to be deactivated on your
            site.
            <br>
            If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
            <br>
            If you don't currently have it you can get it from <a target="_blank" href="https://www.gravityforms.com/pricing/">here.</a>
        </p>
    </section>

<?php
endif;
?>

<?php
if (!$forms && $GF_LICENSE):
	?>

    <section class="lgl-section" style="margin-top: 17px;">
        <p class="lgl-msg lgl-error-msg lgl-gf-error-msg">
            Your license includes an integration with the Gravity Forms plugin, which seems to be deactivated on your
            site.
            <br>
            If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
            <br>
            If you don't currently have it you can get it from <a target="_blank" href="https://www.gravityforms.com/pricing/">here.</a>
        </p>
    </section>

<?php
endif;
?>

<section class="lgl-section">

    <h1>Settings</h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=arcada-labs-little-green-light-data-sync-settings" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">LGL Keys</a>
        <a href="?page=arcada-labs-little-green-light-data-sync-settings&tab=gravity-forms" class="nav-tab <?php if($tab==='gravity-forms'):?>nav-tab-active<?php endif; ?>">Gravity Forms</a>
        <a href="?page=arcada-labs-little-green-light-data-sync-settings&tab=woocommerce" class="nav-tab <?php if($tab==='woocommerce'):?>nav-tab-active<?php endif; ?>">WooCommerce</a>
    </nav>

</section>

<section class="lgl-section">

	<?php
	switch ($tab) {
		case 'woocommerce':
			include('settings-tabs/woocommerce.php');
			break;
		case 'gravity-forms':
			include('settings-tabs/gravity-forms.php');
			break;
		default:
			include('settings-tabs/licenses.php');
			break;
	}
	?>

</section>
