<?php
$ordering = ! empty( get_post_meta ( $this->campaign_id, 'ordering', true  ) ) ? get_post_meta ( $this->campaign_id, 'ordering', true  ) : array(
	'amount',
	'cause',
	'tribute',
	'gift-aid',
	'extra-fee',
	'subscription',
	'main-goal',
	'button',
	'extra-fee-summary'
);
?>
<ul id="donation-block-sortable" data-campaign_id="<?php esc_attr_e($this->campaign_id); ?>">
	<?php
	if ( is_array( $ordering ) && count( $ordering ) > 0 ) {
		foreach ( $ordering as $block ) {
			$block_name = 'Donation ' . str_replace('-', ' ', $block);
			$block_name = str_replace('main', '', $block_name);
			?>
			<li id="<?php echo esc_attr( $block ); ?>" class="ui-state-default" ><?php echo esc_html( $block_name ); ?></li>
			<?php
		}
	}
	?>
</ul>
