<?php
$campaign = $campaign ?? '';
$field_name = $field_name ?? '_lgl_campaign';
//$campaigns = ArcadaLabs\Constants\Dropdowns::CAMPAIGNS;
$options = array();
if ($campaigns->items ?? false) {
    foreach ($campaigns->items as $option) {
        $options[$option->name] = $option->name;
    }
}
?>

<div class="form-group lgl-campaigns">
    <label for="_lgl_campaign">Campaign</label>
    <select class="form-control" name="<?php echo $field_name; ?>">
        <option  <?php if($campaign == '' ) echo 'selected' ?> value="">-- No Campaign --</option>
        <?php foreach ($options as $key => $label): ?>
            <option <?php if($campaign == $key ) echo 'selected' ?> value="<?php echo $key ?>"><?php echo $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
