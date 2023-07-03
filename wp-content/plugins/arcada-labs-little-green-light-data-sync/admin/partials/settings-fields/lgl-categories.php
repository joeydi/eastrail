<?php
$category = $category ?? '';
$field_name = $field_name ?? '_lgl_category';
//$categories = ArcadaLabs\Constants\Dropdowns::CATEGORIES;
$options = array();

if ($categories->items ?? false) {
    foreach ($categories->items as $option) {
        $options[$option->display_name] = $option->display_name;
    }
}
?>

<div class="form-group">
    <label for="_lgl_category">Category</label>
    <select class="form-control" name="<?php echo $field_name; ?>">
        <option  <?php if($category == '' ) echo 'selected' ?> value="">-- No Category --</option>
        <?php foreach ($options as $key => $label): ?>
            <option <?php if($category == $key ) echo 'selected' ?> value="<?php echo $key ?>"><?php echo $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
