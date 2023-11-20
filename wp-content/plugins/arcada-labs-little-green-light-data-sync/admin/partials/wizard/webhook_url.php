<div id="arcada-lgl-wizard-step-3" class="arcada-labs-lgl-webhook-url arcada-lgl-wizard arcada-lgl-wizard--step">
    <h2><a class="arcada-labs-wizard--anchor" step="3" href="#">Webhook URL</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

        <p>
            We’ll need to perform some initial setup in LGL. We promise, it’s easier than it looks!
        </p>

        <ol>
            <li>
                First copy the following template:
                <div class="arcada-lgl-wizard-code-block-container">

                    <ul class="arcada-lgl-wizard-code-block">
                        <li>first_name</li>
                        <li>last_name</li>
                        <li>email</li>
                        <li>phone_number</li>
                        <li>street</li>
                        <li>address_line_1</li>
                        <li>address_line_2</li>
                        <li>address_line_3</li>
                        <li>city</li>
                        <li>postal_code</li>
                        <li>country</li>
                        <li>state</li>
                        <li>gift_type_name</li>
                        <li>campaign_name</li>
                        <li>fund_name</li>
                        <li>category_name</li>
                        <li>external_id</li>
                        <li>received_date</li>
                        <li>payment_type_name</li>
                        <li>received_amount</li>
                        <li>deductible_amount</li>
                        <li>deposited_amount</li>
                        <li>external_constituent_id</li>
                        <li>note_text</li>
                    </ul>

                    <a href="#" class="lgl-sync-copy copy-txt">Copy</a>

                </div>
            </li>
            <li>Then head over to <a target="_blank" href="<?php echo ($lgl_dashboard ?? '') . '/settings/integration/custom_integrations'; ?>"><?php echo ($lgl_dashboard ?? '') . '/settings/integration/custom_integrations'; ?></a></li>
            <li>Click “Add New Integration”</li>
            <li>In the title, enter “ARC Hook””</li>
            <li>In the text box below labeled “Custom Integration Fields”, paste the template you copied in step 1 and click “Save“</li>
            <li>
                <div class="lgl-wizard-instruction">
                    You’ll be taken back to the Custom Integrations page, from there copy the newly created “ARC Hook” value and paste it here:
                </div>
                <div class="arcada-lgl-wizard-block-container">
                    <input
                            type="text"
                            name="arcada_labs_lgl_wizard_webhook_url"
                            id="arcada_labs_lgl_wizard_webhook_url"
                            value="<?php echo get_option('arcada_labs_lgl_webhook_url')?>"
                            class="lgl-sync-settings-input"
                    >
                    <a href="#" class="lgl-sync-paste paste-txt">Paste</a>
                </div>
            </li>
            <li>From the Custom Integrations page you'll see the button to map the fields you've just added, click on the "Update field map" button</li>
            <li>
                <div class="lgl-wizard-instruction">
                    Map the fields to match what each entry field will represent
                </div>
                <div modal-target="arcada-lgl-demo-img" class="arcada-lgl-wizard-block-container arcada-lgl-modal-open">
                    <img class="arcada-lgl-demo-mini" src="<?php echo $url; ?>images/LGL-field-map-mini.jpg"
                         alt="Miniature demo image on how to map the fields on your Little Green Light">
                </div>
            </li>
        </ol>

        <div id="arcada-lgl-demo-img" class="arcada-lgl-modal lgl-hide">
            <div class="arcada-lgl-modal__container arcada-lgl-modal__container--large">
                <h2>Map fields from source</h2>

                <p class="arcada-lgl-modal__content">
                    Make sure that your "Map fields from source" matches the following.
                </p>

                <img src="<?php echo $url; ?>images/LGL-field-map.jpg"
                     alt="Demo image on how to map the fields on your Little Green Light">

                <div class="arcada-lgl-modal__actions">
                    <button type="button" class="lgl-button arcada-lgl-modal-close">Close</button>
                </div>
            </div>
        </div>


        <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
            Loading...&nbsp;
            <span class="lgl-spinner-container">
                <span class="lgl-spinner lgl-spinner-line-1"></span>
                <span class="lgl-spinner lgl-spinner-line-2"></span>
            </span>
        </button>

        <button type="button" id="arcada-labs-lgl-webhook-button" class="lgl-button lgl-button-primary">Save</button>

    </div>
</div>
