<?php
$prod_id = get_post_meta($campaign_id, 'wc_donation_product', true);

if ($prod_id) {
	$product_post = get_post($prod_id);

	if ($product_post) {
		$campaign_description = $product_post->post_content;
	}
}

if (!empty($campaign_description)) {
	?>
	<div class="row0">
		<h3 class="wc-donation-description"></h3>
		<div class="campaign_description-wrapper">
			<?php echo wp_kses_post( wpautop( $campaign_description ) ); ?>
		</div>
	</div>
	<?php 
}
?>
