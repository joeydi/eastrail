<?php 
$tributes = !empty( get_post_meta ( $this->campaign_id, 'tributes', true  ) ) ? get_post_meta ( $this->campaign_id, 'tributes', true  ) : array();
?>
<div id="wc-donation-tribute-wrapper" class="display-wrapper">
	<?php		
	if ( ! empty( $tributes ) ) {				
		foreach ( $tributes as $key => $val ) {
			?>
			<div class="tribute" id="tribute-<?php echo esc_attr($key); ?>">
				<div class="tribute-wrapper">
					<a href="#" class="dashicons dashicons-trash tribute-delete"></a>
					<h4><?php echo esc_html__('Tribute', 'wc-donation'); ?></h4>
					<div class="select-wrapper">
						<label class="wc-donation-label" for="tribute-<?php echo esc_attr($key); ?>"><?php echo esc_attr( __( 'Label', 'wc-donation' ) ); ?></label>
						<input type="text" id="tribute-<?php echo esc_attr($key); ?>" Placeholder="<?php echo esc_html__('Enter Label', 'wc-donation'); ?>" name="tributes[]" value="<?php echo esc_attr($val); ?>">
					</div>
				</div>
			</div>
			<?php
		}
	}
	?>

	<a href="#" id="tribute-add-more"><?php echo esc_attr( __( 'Add Level', 'wc-donation' ) ); ?></a>
</div>
