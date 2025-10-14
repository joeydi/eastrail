<?php
if ( ! empty( $donation_product ) ) { 
	$url                    = get_permalink( $donation_product ); 
	$product                = wc_get_product( $donation_product );
	$campaign_cover_image   = wp_get_attachment_url( get_post_meta( $campaign_id, '_thumbnail_id', true ) );
	$image_url              = ( $campaign_cover_image ) ? $campaign_cover_image : wp_get_attachment_url( $product->get_image_id() );
	$description                = $product->get_description(); ?>

	<div id="social-share-popup" class="social-share-popup">
		<div class="social-share-content">
			<span class="close">&times;</span>
			<h2><?php esc_html_e( 'Help by sharing', 'wc-donation' ); ?></h2>
			<p><?php esc_html_e( 'Share your campaign on social media using the following links.', 'wc-donation' ); ?></p>
			<div class="row">
				
				<div class="column">
					<a href="#" data-url="<?php echo esc_url( $url ); ?>" id="copy-link"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/link.png' ); ?>" alt="Copy Link"><?php esc_html_e( 'Copy link', 'wc-donation' ); ?></a>
				</div>

				<div class="column">
					<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( $url ); ?>" target="_blank"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/facebook.png' ); ?>" alt="Facebook"><?php esc_html_e( 'Facebook', 'wc-donation' ); ?></a>
				</div>
				
				<div class="column">
					<a href="https://wa.me/?text=<?php echo esc_url( $url ); ?>" target="_blank"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/whatsapp.png' ); ?>" alt="WhatsApp"><?php esc_html_e( 'WhatsApp', 'wc-donation' ); ?></a>
				</div>
				
				<div class="column">
					<a href="mailto:?subject=Check out this campaign&body=<?php echo esc_url( $url ); ?>"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/email (1).png' ); ?>" alt="Email"><?php esc_html_e( 'Email', 'wc-donation' ); ?></a>
				</div>
				
				<div class="column">
					<a href="https://www.facebook.com/dialog/send?link=<?php echo esc_url( $url ); ?>" target="_blank"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/messenger.png' ); ?>" alt="Messenger"><?php esc_html_e( 'Messenger', 'wc-donation' ); ?></a>
				</div>
				
				<div class="column">
					<a href="https://twitter.com/intent/tweet?url=<?php echo esc_url( $url ); ?>&text=Check out this campaign" target="_blank"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/twitter.png' ); ?>" alt="X (formerly Twitter)"><?php esc_html_e( 'X', 'wc-donation' ); ?></a>
				</div>
				
				<div class="column">
					<a download href="<?php echo esc_url( WC_DONATION_URL . 'vendor/deps/qr-codes/' . get_post_meta( $campaign_id, 'wc-donation-social-share-qr-url', true ) ); ?>" id="generate-qr"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/qr-code.png' ); ?>" alt="QR Code"><?php esc_html_e( 'QR Code', 'wc-donation' ); ?></a>
				</div>
				
				<div class="column">
					<a href="#" id="print-poster"><img src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/printer.png' ); ?>" alt="Print Poster"><?php esc_html_e( 'Print Poster', 'wc-donation' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<!-- Hidden print section -->
	<div id="print-section" style="display: none;">
		<h1><?php esc_html_e( 'Product Information', 'wc-donation' ); ?></h1>
		<p><strong><?php esc_html_e( 'Product URL:', 'wc-donation' ); ?></strong> <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a></p>
		<?php if ( $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="Product Image" style="max-width: 100%;">
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<p><?php echo wp_kses_post( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}