<h3><?php printf( '%d Message%s for %s', esc_attr( $count ), ( $count > 1 ? 's' : '' ), esc_attr( get_the_title() ) ); ?></h3>
<div class="wc-donation-tribute-wall">

	<?php foreach ( $tribute_wall as $tribute ) : ?>
		<?php 
			$guest_user = empty( $tribute['user_id'] ) ? true : false;
			$user = new WP_User( $tribute['user_id'] ); 
		?>

		<div class="tribute-wall-item">
			<?php echo get_avatar( $tribute['user_id'], 64 ); ?>
			<div class="tribute-meta">
				<div class="tribute-wall-title">
					<div>
						<p><span><?php ( $guest_user ) ? esc_html_e( 'Anonymous', 'wc-donation' ) : esc_attr_e( $user->user_email ); ?></span> &nbsp;-&nbsp; <?php esc_attr_e( $tribute['tribute_name'] ); ?></p>
						<p class="tribute-timestamp"><?php echo esc_attr( date_i18n( 'M d, Y', $tribute['timestamp'] ) ); ?></p>
					</div>
					<div>
						<p><?php esc_attr_e( $tribute['tribute'] ); ?></p>
					</div>
				</div>
				<div class="tribute-message">
					<p><?php echo esc_attr( $tribute['message'] ); ?></p>
				</div>
			</div>
		</div>
	<?php endforeach; ?>

</div>
<style>
	.tribute-wall-item {
		margin-bottom: 40px;
		display: flex;
		align-items: start;
	}

	.tribute-wall-item img {
		display: inline !important;
		max-width: inherit !important;
		margin-right: 40px;
	}
	
	.tribute-wall-title {
		width: 100%;
		display: flex;
		justify-content: space-between;
	}
	
	.tribute-wall-title div p {
		margin-bottom: 0 !important;
		font-size: 18px;
	}

	p.tribute-timestamp {
		font-size: 15px !important;
		color: #b9b9b9;
	}

	.tribute-meta {
		width: 100%;
	}

	.tribute-message {
		margin-top: 20px;
	}

	.tribute-message p {
		margin-top: 5px !important;
		margin-bottom: 0 !important;
	}

	.tribute-wall-title div p span {
		font-weight: 600;
	}
	

</style>
