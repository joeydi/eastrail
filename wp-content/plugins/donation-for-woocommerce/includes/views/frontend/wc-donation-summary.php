<div class="wc-donation-summary-wrapper">
	<h3 class="wc-donation-summary-title"><?php esc_html_e( $summary_title ); ?></h3>
	<p class="wc-donation-summary-desc"><?php esc_html_e( $summary_desc ); ?></p>
	<div class="wc-donation-summary-calc">
		<span class="currency"><?php echo esc_attr($currency_symbol); ?></span>
		<?php 
		$arr  = str_split($donation_amount);
		if ( is_array( $arr ) && count( $arr ) > 0 ) {
			foreach ( $arr as $digit ) {
				if ( '.' === $digit ) {
					$class = 'digit dot';
				} else {
					$class = 'digit';
				}
				?>
				<span class="<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $digit ); ?></span>
				<?php
			}
		}
		?>
	</div>
</div>
