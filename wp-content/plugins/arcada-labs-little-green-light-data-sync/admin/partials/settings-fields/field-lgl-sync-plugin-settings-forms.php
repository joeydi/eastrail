<?php

$name = $args['id'];
$value = get_option($name);
$forms  = class_exists('GFAPI') ? GFAPI::get_forms() : false;
$url = plugin_dir_url(__FILE__);
$url = explode('settings-fields', $url);
$url = $url[0];

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
    $initialized = $value['form'] ?? [];
    ?>

    <p>
        You can track donors, contacts, donations or other transactions.
        Simply select the forms you want to sync.
    </p>

    <p>
        Below, please click “Add form” and configure each form you’d like to be included within LGL sync.
    </p>

    <div class="lgl-form-group-fields-container lgl-hide">

        <hr>

        <div class="lgl-form-group">

            <div class="form-group lgl-gf-form">

                <label for="<?php echo $name ?>[]">Gravity Form</label>

                <select name="<?php echo $name ?>[form][]" id="<?php echo $name ?>" class="">
                    <option value="">-- No Form --</option>
                    <?php foreach ($forms as $form) : ?>

                        <option value="<?php echo $form['id'] ?>"><?php echo $form['title'] ?></option>

                    <?php endforeach ?>
                </select>

                <small class="lgl-input-instructions">Choose the form with which you wish to sync its entries.</small>

            </div>

            <p>
                Select the values that should apply to the registries of this form from the following fields that
                match the attributes of Gifts on your LGL account.
                <br>
                If the form does not use a Total field, you may leave them blank.
                <br>
                <br>
            </p>


            <?php include 'lgl-categories.php'; ?>
            <?php include 'lgl-campaigns.php'; ?>
            <?php include 'lgl-funds.php'; ?>
            <?php include 'lgl-gift-types.php'; ?>
            <?php include 'lgl-payment-types.php'; ?>

        </div>

        <button type="button" class="lgl-button lgl-remove"><span>Remove <img class="arcada-lgl-icon" src="<?php echo $url; ?>images/icons/delete.svg" alt="Little Green Light" width="300" /></span></button>

    </div>


    <?php
    $selected_forms = $value['form'] ?? [];
    $selected_categories = $value['category'] ?? [];
    $selected_campaigns = $value['campaign'] ?? [];
    $selected_funds = $value['fund'] ?? [];
    $selected_gift_types = $value['gift_type'] ?? [];
    $selected_payment_types = $value['payment_type'] ?? [];
    foreach ($selected_forms as $index => $selection): ?>
        <div class="lgl-form-group-fields-container">

            <hr>

            <div class="lgl-form-group">

                <div class="form-group lgl-gf-form">

                    <label for="<?php echo $name ?>[]">Gravity Form</label>

                    <select name="<?php echo $name ?>[form][]" id="<?php echo $name ?>" class="">
                        <option value="">-- No Form --</option>
                        <?php foreach ($forms as $form) : ?>

                            <option <?php if($form['id'] == $selection) { echo "selected"; } ?> value="<?php echo $form['id'] ?>"><?php echo $form['title'] ?></option>

                        <?php endforeach ?>
                    </select>

                    <small class="lgl-input-instructions">Choose the form with which you wish to sync its entries.</small>

                </div>

                <p>
                    Select the values that should apply to the registries of this form from the following fields that
                    match the attributes of Gifts on your LGL account.
                    <br>
                    If the form does not use a Total field, you may leave them blank.
                    <br>
                    <br>
                </p>

                <?php
                $field_name = $name . '[category][]';
                $category = $selected_categories[$index] ?? '';
                include 'lgl-categories.php';
                ?>

                <?php
                $field_name = $name . '[campaign][]';
                $campaign = $selected_campaigns[$index] ?? '';
                include 'lgl-campaigns.php';
                ?>

                <?php
                $field_name = $name . '[fund][]';
                $fund = $selected_funds[$index] ?? '';
                include 'lgl-funds.php';
                ?>

                <?php
                $field_name = $name . '[gift_type][]';
                $gift_type = $selected_gift_types[$index] ?? '';
                include 'lgl-gift-types.php';
                ?>

                <?php
                $field_name = $name . '[payment_type][]';
                $payment_type = $selected_payment_types[$index] ?? '';
                include 'lgl-payment-types.php';
                ?>

            </div>

            <button type="button" class="lgl-button lgl-remove"><span>Remove <img class="arcada-lgl-icon" src="<?php echo $url; ?>images/icons/delete.svg" alt="Little Green Light" width="300" /></span></button>

        </div>
    <?php endforeach; ?>

    <hr>

    <button lgl-clone-target=".lgl-form-group-fields-container" id="lgl-add-form" type="button" class="wf-btn wf-btn-default wf-btn-sm lgl-button">Add Form</button>

    <p class="lgl-disclaimer">
        <small>*For all Gift Types with no selection "Gift" will be used.</small>
        <br>
        <small>*For all Payment Types with no selection "Credit Card" will be used.</small>
    </p>
<?php
endif;
?>

