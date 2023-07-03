<div id="arcada-lgl-wizard-step-5" class="arcada-labs-lgl-wc-products arcada-lgl-wizard arcada-lgl-wizard--step">

    <h2><a class="arcada-labs-wizard--anchor" step="5" href="#">WooCommerce products configuration</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

	    <?php if($WC_LICENSE):?>

            <?php
            if (class_exists('woocommerce')):
            ?>

            <p>
                Each WooCommerce product the purchase of which you’d like to be synchronized into LGL will need to be set up individually.
                Check out this video tutorial below.
            </p>

            <div class="arcada-lgl-video-container">
                <video controls>
                    <source src="<?php echo $url; ?>videos/WC-Tutorial.mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
                Loading...&nbsp;
                <span class="lgl-spinner-container">
                    <span class="lgl-spinner lgl-spinner-line-1"></span>
                    <span class="lgl-spinner lgl-spinner-line-2"></span>
                </span>
            </button>

            <a type="" target="_blank" href="/wp-admin/edit.php?post_type=product" class="lgl-button">Take me there</a>

            <br>
            <hr>

            <p>
                We also need the Payment Type that will be used for your WooCommerce transactions.
            </p>

            <?php
            $payment_type = get_option('arcada_labs_lgl_sync_wc_payment_type');
            $options = array();
            if ($payment_types->items ?? false) {
                foreach ($payment_types->items as $option) {
                    $options[$option->name] = $option->name;
                }
            }
            ?>

            <div class="form-group lgl-campaigns">
                <select id="arcada_labs_lgl_wizard_wc_payment_step" class="form-control" name="arcada_labs_lgl_wizard_wc_payment_step">
                    <option  <?php if($payment_type == '' ) { echo 'selected'; } ?> value="">-- Select --</option>
                    <?php foreach ($options as $key => $label): ?>
                        <option <?php if($payment_type == $key ) { echo 'selected'; } ?> value="<?php echo $key ?>"><?php echo $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <p><small>*"Credit Card" will be used as default if left empty.</small></p>

            <button id="arcada-labs-lgl-wc-products-button" type="button" class="lgl-button lgl-button-primary">Save</button>

            <?php else: ?>

                <p class="lgl-msg lgl-error-msg lgl-gf-error-msg">
                    Your license includes an integration with the WooCommerce plugin, which seems to be deactivated on your
                    site.
                    <br>
                    If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
                    <br>
                    If you don't currently have it you can get it from <a target="_blank" href="/wp-admin/plugin-install.php?s=woocommerce&tab=search&type=term">here.</a>
                </p>

            <?php endif; ?>

	    <?php else: ?>

            <p>
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

            <button id="arcada-labs-lgl-wc-products-button" type="button" class="lgl-button lgl-button-primary">Next step</button>

        <?php endif; ?>

    </div>

</div>