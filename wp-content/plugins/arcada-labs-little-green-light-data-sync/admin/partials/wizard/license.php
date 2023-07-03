<div id="arcada-lgl-wizard-step-1" class="arcada-labs-lgl-license arcada-lgl-wizard arcada-lgl-wizard--step">

    <h2><a class="arcada-labs-wizard--anchor" step="1" href="#">Product License</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

        <p>
            Please enter your license key below.
            <br>
            If you’ve already purchased a license, your key can be accessed from your
            <a target="_blank" href="https://arcadalabs.com/my-account/downloads">dashboard</a>
            <br>
            If you haven’t, you can do so from
            <a target="_blank" href="https://arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl">arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl</a>.

        </p>

        <div class="arcada-lgl-wizard-block-container lgl-wizard-instruction">
            <input
                    type="text"
                    name="arcada_labs_lgl_wizard_license_step"
                    id="arcada_labs_lgl_wizard_license_step"
                    value="<?php echo get_option('arcada_labs_lgl_sync_license_key')?>"
                    class="lgl-sync-settings-input"
            >
            <a href="#" class="lgl-sync-paste paste-txt">Paste</a>
        </div>

        <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
            Loading...&nbsp;
            <span class="lgl-spinner-container">
                <span class="lgl-spinner lgl-spinner-line-1"></span>
                <span class="lgl-spinner lgl-spinner-line-2"></span>
            </span>
        </button>

        <button type="button" id="arcada-labs-lgl-license-button" class="lgl-button lgl-button-primary">Save</button>

    </div>
</div>