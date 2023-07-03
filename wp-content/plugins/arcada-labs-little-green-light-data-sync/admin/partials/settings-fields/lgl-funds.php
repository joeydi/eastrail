<?php
$fund = $fund ?? '';
$field_name = $field_name ?? '_lgl_fund';
//$funds = ArcadaLabs\Constants\Dropdowns::FUNDS;
$options = array();
if ($funds->items ?? false) {
    foreach ($funds->items as $option) {
        $options[$option->name] = $option->name;
    }
}
?>

<div class="form-group lgl-funds-group">
    <label for="_lgl_fund">Fund</label>
    <select class="form-control" name="<?php echo $field_name; ?>">
        <option  <?php if($fund == '' ) echo 'selected' ?> value="">-- No Fund --</option>
        <?php foreach ($options as $key => $label): ?>
            <option <?php if($fund == $key ) echo 'selected' ?> value="<?php echo $key ?>"><?php echo $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
