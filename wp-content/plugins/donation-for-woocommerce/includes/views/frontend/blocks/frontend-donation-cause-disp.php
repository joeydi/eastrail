<?php
if ( 'show' === $causeDisp && !empty( $causeNames[0] ) ) {
	?>
	<div class="row2">
		<h3 class="wc-donation-title"><?php echo esc_html__('Select Cause', 'wc-donation' ); ?></h3>
		<div class="cause-wrapper after">
			<ul class="causes-dropdown">
				<li class="init"><?php echo esc_html__('--Select Cause--', 'wc-donation'); ?></li>
				<?php 
				foreach ( $causeNames[0] as $key => $value ) {
					$cause_img = !empty( $causeImg[0][$key] ) ? $causeImg[0][$key] : WC_DONATION_URL . 'assets/images/no-image-cause.jpg';
					$cause_desc = !empty( $causeDesc[0][$key] ) ? $causeDesc[0][$key] : ''; 
					?>
					<li class="dropdown-item" data-id="cause-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" data-name="<?php echo esc_attr( $value ); ?>">
						<div class="cause-drop-content"><div class="cause-img-wrap"><img src="<?php echo esc_attr( $cause_img ); ?>" class="img-cause-drop" width="32px"/></div><div class="cause-text-wrap"><div class="cause-drop-title"><?php echo esc_attr( $value ); ?></div><div class="cause-drop-desc"><?php echo esc_attr( $cause_desc ); ?></div></div></div></li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>
	<?php 
}
