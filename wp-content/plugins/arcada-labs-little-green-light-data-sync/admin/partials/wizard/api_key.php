<div id="arcada-lgl-wizard-step-2" class="arcada-labs-lgl-api-key arcada-lgl-wizard arcada-lgl-wizard--step">
    <h2><a class="arcada-labs-wizard--anchor" step="2" href="#">API Key</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

        <p>
            We’ll need your API Key from LGL. To obtain this:
        </p>

        <ol>
            <li>Head over to: <a target="_blank" href="<?php echo ($lgl_dashboard ?? '') . '/settings/integration/api_keys'; ?>"><?php echo ($lgl_dashboard ?? '') . '/settings/integration/api_keys'; ?></a></li>
            <li>Click “Generate Access Token”</li>
            <li>Copy the key (it should be a lot of random looking letters and numbers)</li>
            <li>
                <div class="lgl-wizard-instruction">Paste the key below</div>
                <div class="arcada-lgl-wizard-block-container">
                    <input
                            type="text"
                            name="arcada_labs_lgl_wizard_api_key"
                            id="arcada_labs_lgl_wizard_api_key"
                            value="<?php echo get_option('arcada_labs_lgl_sync_settings_field_lgl_api_key')?>"
                            class="lgl-sync-settings-input"
                    >
                    <a href="#" class="lgl-sync-paste paste-txt">Paste</a>
                </div>
            </li>
        </ol>



        <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
            Loading...&nbsp;
            <span class="lgl-spinner-container">
                <span class="lgl-spinner lgl-spinner-line-1"></span>
                <span class="lgl-spinner lgl-spinner-line-2"></span>
            </span>
        </button>

        <button type="button" id="arcada-labs-lgl-api-key-button" class="lgl-button lgl-button-primary">Save</button>

    </div>

</div>