<?php 
/**
 * Donation goal HTML
 */

$causeDisp = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-cause-display-option', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-cause-display-option', true  ) : 'disabled';
$causeNames = !empty( get_post_meta ( $this->campaign_id, 'donation-cause-names', false  ) ) ? get_post_meta ( $this->campaign_id, 'donation-cause-names', false  ) : array();
$causeDesc = !empty( get_post_meta ( $this->campaign_id, 'donation-cause-desc', false  ) ) ? get_post_meta ( $this->campaign_id, 'donation-cause-desc', false  ) : array();
$causeImg = !empty( get_post_meta ( $this->campaign_id, 'donation-cause-img', false  ) ) ? get_post_meta ( $this->campaign_id, 'donation-cause-img', false  ) : array();
?>

<div class="select-wrapper goal-display-option">
	<label class="wc-donation-label" for=""><?php echo esc_html__( 'Donation Cause', 'wc-donation' ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_CAUSE() as $key => $value ) {
		if ( $causeDisp == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="<?php esc_attr_e($key); ?>" name="wc-donation-cause-display-option" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="<?php esc_attr_e($key); ?>">
			<span>
				<svg width="12px" height="9px" viewbox="0 0 12 9">
					<polyline points="1 5 4 8 11 1"></polyline>
				</svg>
			</span>
			<span><?php esc_attr_e( $value ); ?></span>
		</label>
		<?php
	}
	?>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip"><?php echo esc_html__('If enable, You can show causes on this campaign.', 'wc-donation'); ?></small>
	</div>
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-cause-name"><?php echo esc_attr( __( 'Cause Name', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-cause-name" Placeholder="<?php echo esc_html__('Enter Donation Cause Name', 'wc-donation'); ?>">
</div>
<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-cause-desc"><?php echo esc_attr( __( 'Cause Description', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-cause-desc" Placeholder="<?php echo esc_html__('Enter Donation Cause Description', 'wc-donation'); ?>">
</div>
<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-cause-img"><?php echo esc_attr( __( 'Cause Thumbnail', 'wc-donation' ) ); ?></label>
	<a href="javascript:void(0);" class="button button-primary button-large donation-cause-thumb-upl"><?php echo esc_html__( 'Upload Thumbnail', 'wc-donation' ); ?></a>
	<input type="hidden" id="wc-donation-cause-thumb" name="donation-cause-thumb" value="">
	<a href="javascript:void(0);" class="donation-cause-thumb-rmv" style="display:none"><span class="dashicons dashicons-trash"></span></a>
</div>

<div class="select-wrapper">
	<a href="javascript:void(0);" id="wcd-add-cause" class="wc-button-primary"><?php echo esc_html__( 'Save', 'wc-donation' ); ?></a>
</div>

<div class="select-wrapper wc-donation-cause-table" id="blk_fixed_amount">
	<table class="wp-list-table widefat fixed striped table-view-list posts campaign-cause">
		<thead>
		<tr>
			<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php echo esc_html__( 'Select All', 'wc-donation' ); ?></label><input id="cb-select-all-1" type="checkbox"></td>
			<th scope="col" id="cause_img" class="manage-column column-cause_img column-primary"><?php echo esc_html__( 'Image', 'wc-donation' ); ?></th>
			<th scope="col" id="cause_name" class="manage-column column-cause_name"><?php echo esc_html__( 'Causes', 'wc-donation' ); ?></th>
			<th scope="col" id="cause_desc" class="manage-column column-cause_desc"><?php echo esc_html__( 'Description', 'wc-donation' ); ?></th>
			<th scope="col" id="actions" class="manage-column column-actions"><?php echo esc_html__( 'Actions', 'wc-donation' ); ?></th>
		</tr>
		</thead>
		<tbody id="the-list" class="type-wc-donation causes-table-body">
			<?php 
			if ( ! empty( $causeNames[0] ) ) {
				$count = 1;
				foreach ( $causeNames[0] as $key => $val ) { 
					$cause_img = !empty($causeImg[0][$key]) ? $causeImg[0][$key] : '';
					?>
				<tr id="post-<?php echo esc_attr( $count ); ?>" class="iedit author-self level-0 post-<?php echo esc_attr( $count ); ?> type-wc-donation-causes status-publish hentry"><th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $count ); ?>">Select <?php echo esc_attr( $val ); ?></label><input id="cb-select-<?php echo esc_attr( $count ); ?>" type="checkbox" value="<?php echo esc_attr( $count ); ?>"></th><td class="campaign_cause_img column-campaign_cause_img has-row-actions column-primary" data-colname="<?php echo esc_html__( 'Image', 'wc-donation' ); ?>"><img class="causes-img" src="<?php echo esc_attr( $cause_img ); ?>"><input type="hidden" class="causes_img" value="<?php echo esc_attr( $cause_img ); ?>" name="donation-cause-img[]"><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="campaign_cause_name column-campaign_cause_name" data-colname="<?php echo esc_html__( 'Causes', 'wc-donation' ); ?>"><input type="text" class="causes_name" value="<?php echo esc_attr( $val ); ?>" name="donation-cause-name[]" readonly></td><td class="campaign_cause_desc column-campaign_cause_desc" data-colname="<?php echo esc_html__( 'Description', 'wc-donation' ); ?>"><input type="text" class="causes_desc" value="<?php echo esc_attr( $causeDesc[0][$key] ); ?>" name="donation-cause-desc[]" readonly></td><td class="actions column-actions" data-colname="<?php echo esc_html__( 'Actions', 'wc-donation' ); ?>"><a href="javascript:void(0);" class="wc-dashicons editIcon cause-edit"> <span class="dashicons dashicons-edit"></span> </a><a href="javascript:void(0);" class="wc-dashicons deleteIcon cause-delete" title="Delete"> <span class="dashicons dashicons-trash"></span></a></td></tr>
					<?php 
					$count++;
				}
			} else { 
				?>
				<tr class="no-items"><td class="colspanchange" colspan="8"><?php echo esc_html__( 'No Causes Found', 'wc-donation' ); ?></td></tr>
			<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td class="manage-column column-delete delete-column"><button class="button button-primary button-large delete-selected-causes"><?php echo esc_html__( 'Delete Selected', 'wc-donation' ); ?></button></td>
		</tr>
	</tfoot>
</table>
</div>
