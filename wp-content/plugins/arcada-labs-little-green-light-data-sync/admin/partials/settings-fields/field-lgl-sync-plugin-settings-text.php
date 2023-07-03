<?php
$name  = $args['id'];
$value = get_option( $name );
?>

<input
    type="text"
    name="<?php echo $name ?>"
    id="<?php echo $name ?>"
    value="<?php echo $value ?>"
    class="lgl-sync-settings-input"
>
