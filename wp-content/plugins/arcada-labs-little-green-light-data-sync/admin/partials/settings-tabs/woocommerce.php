<?php
if ($WC_LICENSE):
    if (class_exists('woocommerce')):
	?>

        <form method="post" action="options.php">
            <?php
            settings_fields('arcada_labs_lgl_sync_settings_wc_payment_type');
            do_settings_sections($this->plugin_name . '-settings-wc-form');
            submit_button();
            ?>
        </form>

        <hr>

        <p class="lgl-promotion">
            In order for the system to synchronize your transactions each product that you wish to track
            needs to be set up.
            <br>
            You can do this individually or by making a bulk edit at your WooCommerce products.
        </p>

        <a type="" target="_blank" href="/wp-admin/edit.php?post_type=product" class="lgl-button">Take me there</a>

    <?php else: ?>

        <h2>WooCommerce plugin missing!</h2>

        <p>
            To access the full functionality of the WooCommerce add-on the plugin must be installed and active.
            <br>
            <br>
            If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
            <br>
            <br>
            If you don't currently have it you can get it from <a target="_blank" href="/wp-admin/plugin-install.php?s=woocommerce&tab=search&type=term">here.</a>
        </p>

    <?php endif; ?>
<?php else: ?>

    <h2>WooCommerce Add-on available!</h2>

    <p class="lgl-promotion">
        If you are selling through WooCommerce we also have an add-on that might interest you.
        <br>
        <br>
        With this add-on you can instantly sync every transaction completed through your WooCommerce store
        (both past and present) as Gifts on the LGL platform – in real time!
        <br>
        <br>
        For each WooCommerce product you’d like to track in LGL, you can choose the category, fund, and campaign
        you want it to sync with, immediately pulling all relevant information into your LGL database and
        slashing time spent on administrative tasks.
        <br>
        <br>
        Get your License at <a target="_blank" href="https://arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl">arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl</a>.
    </p>

<?php endif; ?>
