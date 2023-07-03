<div id="arcada-lgl-overlay" class="arcada-lgl-overlay lgl-hide">
    <div class="lgl-lds-ring"><div></div><div></div><div></div><div></div></div>
    <div id="lgl-overlay-text"></div>
</div>

<div id="arcada-lgl-wizard-step-0" class="arcada-lgl-wizard">
    <div class="lgl-msg lgl-success-msg lgl-wizard-msg"><span></span><a href="#" class="lgl-msg-dismiss">Dismiss</a></div>
    <div class="lgl-msg lgl-error-msg lgl-wizard-msg"><span></span><a href="#" class="lgl-msg-dismiss">Dismiss</a></div>

    <h2><a class="arcada-labs-wizard--anchor" step="0" href="#">Lets get started</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

        <p>
            Welcome! You’re just a few steps away from syncing your site to Little Green Light.
            <br>
            To get started, go to your LGL Dashboard, copy the url address, and paste it here:
            <br>
            It should look something like this: https://your-site-name.littlegreenlight.com
        </p>

        <div class="arcada-lgl-wizard-block-container lgl-wizard-instruction">
            <input
                    type="text"
                    name="arcada_labs_lgl_wizard_start_step"
                    id="arcada_labs_lgl_wizard_start_step"
                    value="<?php echo get_option('arcada_labs_lgl_sync_dashboard_link')?>"
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

        <button id="arcada-labs-lgl-start-button" class="lgl-button lgl-button-primary">Continue</button>

    </div>
</div>
