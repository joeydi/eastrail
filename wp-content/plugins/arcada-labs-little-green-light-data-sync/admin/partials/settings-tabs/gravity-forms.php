<?php
if ($GF_LICENSE):
	if(!$forms):
		?>

    <h2>Gravity Forms plugin missing!</h2>

    <p>
        To access the full functionality of the Gravity Forms add-on the plugin must be installed and active.
        <br>
        <br>
        If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
        <br>
        <br>
        If you don't currently have it you can get it from <a target="_blank" href="https://www.gravityforms.com/pricing/">here.</a>
    </p>

	<?php else: ?>

        <h2>Gravity Forms</h2>

        <p>
            All forms selected must at least contain: <strong>name</strong>, and <strong>email</strong> field types,
            in order to be synced to Little Green Light properly.
            <br>
            All forms selected that don't fulfill this condition will not be synced.
        </p>

        <form class="lgl-gf-forms-form" method="post" action="options.php">
			<?php
			settings_fields('arcada_labs_lgl_sync_settings_g_form');
			do_settings_sections($this->plugin_name . '-settings-g-form');
            submit_button('Save', 'submit', 'submit', true, array('id'=>'arcada-labs-lgl-forms-button', 'class'=>'lgl-button lgl-button-primary'));
			?>
        </form>

        <hr>


	<?php endif; ?>
<?php else: ?>

    <p class="lgl-promotion">
        Are you using Gravity Forms to track donors and other contacts?
        Maybe you even have forms to receive donations and other transactions?
        With the Gravity Forms add-on, you’ll be able to automatically transfer this data to your LGL account
        on an on-going basis, for streamlined and organized fund management without wasting a minute.
        <br>
        <br>
        Just select the forms you want to sync, and the Funds/categories they apply to,
        and you’ll have your Constituents and Gifts updated in an instant.
        <br>
        <br>
        Yes – it’ll even sync your past data! Say goodbye to hours spent slogging through spreadsheets.
        <br>
        <br>
        Get your License at <a target="_blank" href="https://arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl">arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl</a>.
    </p>

<?php endif; ?>
