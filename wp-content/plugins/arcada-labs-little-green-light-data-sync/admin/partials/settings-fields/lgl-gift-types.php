<?php
$gift_type = $gift_type ?? '';
$field_name = $field_name ?? '_lgl_gift_type';
$options = array();
if ($gift_types->items ?? false) {
    foreach ($gift_types->items as $option) {
        $options[$option->name] = $option->name;
    }
}
?>

<div class="form-group lgl-campaigns">
    <label for="<?php echo $field_name; ?>">Gift Type</label>
    <select class="form-control" name="<?php echo $field_name; ?>">
        <option  <?php if($gift_type == '' ) { echo 'selected'; } ?> value="">-- Select --</option>
        <?php foreach ($options as $key => $label): ?>
            <option <?php if($gift_type == $key ) { echo 'selected'; } ?> value="<?php echo $key ?>"><?php echo $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
