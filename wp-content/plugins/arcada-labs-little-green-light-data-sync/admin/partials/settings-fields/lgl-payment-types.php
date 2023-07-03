<?php
$payment_type = $payment_type ?? '';
$field_name = $field_name ?? '_lgl_payment_type';
$options = array();
if ($payment_types->items ?? false) {
    foreach ($payment_types->items as $option) {
        $options[$option->name] = $option->name;
    }
}
?>

<div class="form-group lgl-campaigns">
    <label for="<?php echo $field_name; ?>">Payment Type</label>
    <select class="form-control" name="<?php echo $field_name; ?>">
        <option  <?php if($payment_type == '' ) { echo 'selected'; } ?> value="">-- Select --</option>
        <?php foreach ($options as $key => $label): ?>
            <option <?php if($payment_type == $key ) { echo 'selected'; } ?> value="<?php echo $key ?>"><?php echo $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
