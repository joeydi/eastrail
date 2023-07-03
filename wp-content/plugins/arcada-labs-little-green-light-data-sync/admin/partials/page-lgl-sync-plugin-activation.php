<?php
include 'header.php';
?>

<section class="lgl-section">

    <h1>Product Activation</h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('arcada_labs_lgl_sync_activation');
        do_settings_sections($this->plugin_name . '-activation');
        ?>
        <p class="submit">
            <button class="button button-primary" type="button" id="license-activation-btn">Save Changes</button>
            <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
                Validating...&nbsp;
                <span class="lgl-spinner-container">
                    <span class="lgl-spinner lgl-spinner-line-1"></span>
                    <span class="lgl-spinner lgl-spinner-line-2"></span>
                </span>
            </button>
        </p>
    </form>

    <?php if(!empty(get_option('arcada_labs_lgl_sync_license_key'))): ?>
    <hr>

    <p>If you want to transfer your license to another site, you can do so by <a id="license-deactivation-a" href="#">first disabling it here.</a></p>

    <p>If you want to run the initial steps wizard again, you can do so <a id="wizard-rerun-a" href="#">clicking here.</a></p>

    <button class="button lgl-hide" type="button" id="license-deactivation-btn">Deactivate License</button>
    <button class="button lgl-hide" type="button" id="wizard-rerun">Re-run wizard</button>
    <br>
    <br>
    <?php endif ?>

    <div class="lgl-msg lgl-success-msg"><span></span><a href="#" class="lgl-msg-dismiss">Dismiss</a></div>
    <div class="lgl-msg lgl-error-msg"><span></span><a href="#" class="lgl-msg-dismiss">Dismiss</a></div>

</section>