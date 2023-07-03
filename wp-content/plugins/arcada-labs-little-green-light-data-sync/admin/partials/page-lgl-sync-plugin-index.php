<?php
include 'header.php';
?>
<div id="arcada-lgl-overlay" class="arcada-lgl-overlay lgl-hide">
    <div class="lgl-lds-ring"><div></div><div></div><div></div><div></div></div>
    <div id="lgl-overlay-text"></div>
</div>

<section class="lgl-section">

    <h1>Manual Sync Options</h1>

    <p>
        If you skipped the initial sync, or need to run it again, you can do that from here.
    </p>

    <br>

    <div id="lgl-constituent-sync" class="lgl-sync-button-container">

        <button modal-target="arcada-lgl-customer-sync" class="lgl-btn button arcada-lgl-modal-open">
            Sync Constituents
        </button>

        <button id="lgl-constituent-sync-spinner" class="lgl-button lgl-spinner-container lgl-hide">
            Sync running...&nbsp;
            <span class="lgl-spinner-container">
                <span class="lgl-spinner lgl-spinner-line-1"></span>
                <span class="lgl-spinner lgl-spinner-line-2"></span>
            </span>
        </button>

        <p>
            This process will pull all your WordPress customers and subscribers, and create them as Constituents in your
            Little Green Light account. If the Constituent already exists, their contact information will be updated.
        </p>

        <div id="arcada-lgl-customer-sync" class="arcada-lgl-modal lgl-hide">
            <div class="arcada-lgl-modal__container">

                <h3 class="arcada-lgl-modal__title">Before you run your sync!</h3>

                <p class="arcada-lgl-modal__content">
                    You are about to begin a process that will send all of your customers’ and subscribers’ information to your Little Green Light account.
                </p>

                <p class="arcada-lgl-modal__content">
                    This may take several minutes depending on the amount of users that currently exist on your site.
                </p>

                <p class="arcada-lgl-modal__content text-bold">Are you sure you want to proceed?</p>

                <div class="arcada-lgl-modal__actions">
                    <button type="button" class="lgl-button arcada-lgl-modal-close">Cancel</button>
                    <button type="button" class="lgl-button lgl-button-primary" id="lgl-constituent-sync-btn">Yes, Start the Sync</button>
                </div>

            </div>
        </div>

        <?php if ($WC_LICENSE || $GF_LICENSE): ?>
        <hr>
        <?php endif; ?>

    </div>

    <?php if ($WC_LICENSE): ?>

        <?php if (!class_exists('woocommerce')): ?>

            <p class="lgl-msg lgl-error-msg lgl-gf-error-msg">
                Your license includes an integration with the Gravity Forms plugin, which seems to be deactivated on your
                site.
                <br>
                If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
                <br>
                If you don't currently have it you can get it from <a target="_blank" href="/wp-admin/plugin-install.php?s=woocommerce&tab=search&type=term">here.</a>
            </p>

	    <?php else: ?>

        <div id="lgl-transaction-sync" class="lgl-sync-button-container">

            <button modal-target="arcada-lgl-wc-sync" class="lgl-btn button arcada-lgl-modal-open">
                Sync Transactions
            </button>

            <button id="lgl-transaction-sync-spinner" class="lgl-button lgl-spinner-container lgl-hide">
                Sync running...&nbsp;
                <span class="lgl-spinner-container">
                    <span class="lgl-spinner lgl-spinner-line-1"></span>
                    <span class="lgl-spinner lgl-spinner-line-2"></span>
                </span>
            </button>

            <p>
                This process will scan all orders, checking for any products with the option
                “Sync with Little Green Light” selected.
                <br>
                <br>
                For every order containing at least one such product, a new
                Gift will be created in your Little Green Light amount, for the total amount of the product(s) from
                that order selected for sync.
            </p>

            <div id="arcada-lgl-wc-sync" class="arcada-lgl-modal lgl-hide">
                <div class="arcada-lgl-modal__container">

                    <h3 class="arcada-lgl-modal__title">Before you run your sync!</h3>

                    <p class="arcada-lgl-modal__content">
                        You are about to run a synchronization that will send to your Little Green Light account the information
                        of all your WooCommerce transactions.
                    </p>

                    <p class="arcada-lgl-modal__content">
                        This may take several minutes depending on the amount of transactions that currently exist on your site.
                    </p>

                    <p class="arcada-lgl-modal__content text-bold">Are you sure you want to proceed?</p>

                    <div class="arcada-lgl-modal__actions">
                        <button type="button" class="lgl-button arcada-lgl-modal-close">Cancel</button>
                        <button type="button" class="lgl-button lgl-button-primary" id="lgl-transaction-sync-btn">Yes, Start the Sync</button>
                    </div>

                </div>
            </div>

            <?php if ($GF_LICENSE): ?>
                <hr>
            <?php endif; ?>

        </div>

	    <?php endif; ?>

    <?php endif; ?>

    <?php if ($GF_LICENSE): ?>

        <?php
        $forms  = class_exists('GFAPI') ? GFAPI::get_forms() : false;
        if (!$forms):
            ?>

            <p class="lgl-msg lgl-error-msg lgl-gf-error-msg">
                Your license includes an integration with the Gravity Forms plugin, which seems to be deactivated on your
                site.
                <br>
                If you already have it installed, you can activate it <a target="_blank" href="/wp-admin/plugins.php">here.</a>
                <br>
                If you don't currently have it you can get it from <a target="_blank" href="https://www.gravityforms.com/pricing/">here.</a>
            </p>

        <?php
        else :
            ?>

            <div id="lgl-form-sync" class="lgl-sync-button-container">

                <button modal-target="arcada-lgl-gf-sync" class="lgl-btn button arcada-lgl-modal-open">
                    Sync Form Entries
                </button>

                <button class="lgl-form-sync-spinner lgl-button lgl-spinner-container lgl-hide">
                    Sync running...&nbsp;
                    <span class="lgl-spinner-container">
                    <span class="lgl-spinner lgl-spinner-line-1"></span>
                    <span class="lgl-spinner lgl-spinner-line-2"></span>
                </span>
                </button>

                <p>
                    This process will search any form you’ve selected for sync and will create or update Constituents
                    in your Little Green Light account according to their email addresses, create Gifts for any
                    donations or other transactions found in the forms, and automatically organize these into the
                    correct Funds and Categories based on the settings you select in configuration.
                    <br>
                    <br>
                    Go to your
                    <a href="/wp-admin/admin.php?page=arcada-labs-little-green-light-data-sync-settings">settings</a>
                    to add and configure forms for syncing.
                </p>

                <div id="arcada-lgl-gf-sync" class="arcada-lgl-modal lgl-hide">
                    <div class="arcada-lgl-modal__container">

                        <h3 class="arcada-lgl-modal__title">Before you run your sync!</h3>

                        <p class="arcada-lgl-modal__content">
                            You are about to run a synchronization that will send to your Little Green Light account the information
                            of all your selected Gravity Forms.
                        </p>

                        <p class="arcada-lgl-modal__content">
                            This may take several minutes depending on the amount of entries that currently exist on your site.
                        </p>

                        <p class="arcada-lgl-modal__content text-bold">Are you sure you want to proceed?</p>

                        <div class="arcada-lgl-modal__actions">
                            <button type="button" class="lgl-button arcada-lgl-modal-close">Cancel</button>
                            <button type="button" class="lgl-button lgl-button-primary" id="lgl-form-sync-btn">Yes, Start the Sync</button>
                        </div>

                    </div>
                </div>

            </div>
        <?php endif; ?>

    <?php endif ?>

    <div class="lgl-msg lgl-success-msg"><span></span><a href="#" class="lgl-msg-dismiss">Dismiss</a></div>
    <div class="lgl-msg lgl-error-msg"><span></span><a href="#" class="lgl-msg-dismiss">Dismiss</a></div>

</section>