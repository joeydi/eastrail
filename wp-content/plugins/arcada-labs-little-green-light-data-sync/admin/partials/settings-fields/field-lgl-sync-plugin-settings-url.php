<?php
$name  = $args['id'];
$value = get_option( $name );
?>

<div>
    <div class="d-flex">
        <input
                type="text"
                name="<?php echo $name ?>"
                id="<?php echo $name ?>"
                value="<?php echo $value ?>"
                class="lgl-sync-settings-input"
        >
        <button type="button" class="wf-btn wf-btn-default wf-btn-sm lgl-webhook-button">Test hook</button>
    </div>
    <span class="lgl-hook-response"></span>
</div>