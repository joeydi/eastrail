<h3><?php _e("LGL profile information", "blank"); ?></h3>

<table class="form-table">
    <tr>
        <th><label for="lgl_id"><?php _e("LGL ID"); ?></label></th>
        <td>
            <input type="text" name="lgl_id" id="lgl_id" value="<?php echo esc_attr( get_the_author_meta( 'lgl_id', $user->ID ) ); ?>" class="regular-text" /><br />
        </td>
    </tr>
</table>