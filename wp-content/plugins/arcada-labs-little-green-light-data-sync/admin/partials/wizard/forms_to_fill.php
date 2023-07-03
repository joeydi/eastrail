<div id="arcada-lgl-wizard-step-4" class="arcada-labs-lgl-forms arcada-lgl-wizard arcada-lgl-wizard--step">
    <h2><a class="arcada-labs-wizard--anchor" step="4" href="#">Gravity Forms Form Selection</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

        <?php if($GF_LICENSE):?>

            <?php if (!empty(get_option('arcada_labs_lgl_sync_settings_field_lgl_api_key'))): ?>
            <p>
                Gravity Forms is a popular WordPress plugin that allows users to create custom forms and surveys for
                their websites.
                The plugin also offers a wide range of features and integrations such as
                conditional logic, payment gateways, email marketing services, and CRM tools.
                Gravity Forms is widely used for creating contact forms, registration forms, surveys, quizzes,
                and other forms that require user input.
            </p>

            <form id="lgl-gfs" class="arcada-labs-lgl-form" method="post" action="options.php">
                <?php
                settings_fields('arcada_labs_lgl_sync_settings_g_form');
                do_settings_sections($this->plugin_name . '-settings-g-form');
                submit_button('Save', 'submit', 'submit', true, array('id'=>'arcada-labs-lgl-forms-button', 'class'=>'lgl-button lgl-button-primary'));
                ?>
            </form>

            <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
                Loading...&nbsp;
                <span class="lgl-spinner-container">
                    <span class="lgl-spinner lgl-spinner-line-1"></span>
                    <span class="lgl-spinner lgl-spinner-line-2"></span>
                </span>
            </button>

            <!--<button type="button" id="arcada-labs-lgl-forms-button" class="lgl-button lgl-button-primary">Take me there</button>-->

            <?php else: ?>
            <p>
                Whoops! It looks like we are still missing some information required to continue with this step
            </p>
            <?php endif ?>
        <?php else: ?>

            <p>
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

            <button id="arcada-labs-lgl-skip-forms-button" type="button" class="lgl-button lgl-button-primary">Next step</button>
        <?php endif ?>
    </div>

</div>
