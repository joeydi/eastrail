<div id="arcada-lgl-wizard-step-6" class="arcada-labs-lgl-initial-sync arcada-lgl-wizard arcada-lgl-wizard--step">
    <h2><a class="arcada-labs-wizard--anchor" step="6" href="#">Initial Sync</a></h2>

    <div class="arcada-lgl-wizard--content <?php if($completed) { echo 'arcada-lgl-wizard--content-closed'; }?>">

        <?php if(!empty(get_option('arcada_labs_lgl_sync_settings_field_lgl_api_key')) && !empty(get_option('arcada_labs_lgl_webhook_url'))): ?>
        <p>
            You’re all set!
            <br>
            <br>
            To begin the initial sync, just click “Start syncing” below.
            This will synchronize all users currently on the site and all past WooCommerce orders (if applicable)
            into LGL’s integration queue. Don’t worry, you’ll be able to handle conflicts from
            <a target="_blank" href="<?php echo $lgl_dashboard ?? 'your lgl dashboard' ?>/integrations/records/unsaved">LGL’s handy Integration Queue</a>
            before anything is overwritten, where you’ll have to approve all of the syncs.
            <br>
            <br>
            The first time can take a little while but it’s easier as you go. If in the future you'd like anything
            synchronized to your website to be automatically imported into LGL without the queue approval,
            then you can do that by going into your
            <a target="_blank" href="<?php echo $lgl_dashboard ?? 'your lgl dashboard' ?>/settings/integration/custom_integrations">LGL’s custom integrations</a>
            page and remove
            the checkmark on the <strong>Require review?</strong> option.
            <br>
            <br>
            If you’d prefer to skip an initial sync, just click “Skip” below and you’re all done!
            If you’d like to run the sync later, you can do so from the plugin settings page.
        </p>

        <button class="lgl-form-sync-spinner lgl-button lgl-button-primary lgl-spinner-container lgl-hide">
            Loading...&nbsp;
            <span class="lgl-spinner-container">
                <span class="lgl-spinner lgl-spinner-line-1"></span>
                <span class="lgl-spinner lgl-spinner-line-2"></span>
            </span>
        </button>

        <button type="button" id="arcada-labs-lgl-initial-sync-button-skip" class="lgl-button lgl-button--bold">Skip</button>
        <button type="button" modal-target="arcada-lgl-all-sync" class="lgl-button lgl-button--bold lgl-button-primary arcada-lgl-modal-open">Start syncing</button>

        <div id="arcada-lgl-all-sync" class="arcada-lgl-modal lgl-hide">
            <div class="arcada-lgl-modal__container">

                <h3 class="arcada-lgl-modal__title">Before you run your sync!</h3>

                <p class="arcada-lgl-modal__content">
                    You are about to begin a process that will send all of your customers’ and subscribers’ information to your Little Green Light account.
                </p>

                <p class="arcada-lgl-modal__content">
                    This may take several minutes, depending on the amount of users that currently exist on your site.
                </p>

                <p class="arcada-lgl-modal__content text-bold">Are you sure you want to proceed?</p>

                <div class="arcada-lgl-modal__actions">
                    <button type="button" class="lgl-button arcada-lgl-modal-close">Cancel</button>
                    <button type="button" class="lgl-button lgl-button-primary" id="arcada-labs-lgl-initial-sync-button">Yes, Start the Sync</button>
                </div>

            </div>
        </div>

        <?php else: ?>
        <p>
            Whoops! it looks like we are still missing some information to continue.
            Please make sure you've filled out the information on the previous steps before coming back here.
        </p>
        <?php endif ?>
    </div>

</div>