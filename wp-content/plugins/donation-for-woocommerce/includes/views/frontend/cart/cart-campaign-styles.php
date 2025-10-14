<style>
	:root {
		--wc-bg-color: #<?php esc_html_e( $donation_button_color ); ?>;
		--wc-txt-color: #<?php esc_html_e( $donation_button_text_color ); ?>;
	}

	<?php
	if ( 'before' === $where_currency_symbole ) { 
		?>
		#wc_donation_on_cart .price-wrapper::before {
			background: #<?php esc_html_e( $donation_button_color ); ?>;
			color: #<?php esc_html_e( $donation_button_text_color ); ?>;
		}
		<?php
	} else { 
		?>
		#wc_donation_on_cart .price-wrapper::after {
			background: #<?php esc_html_e( $donation_button_color ); ?>;
			color: #<?php esc_html_e( $donation_button_text_color ); ?>;
		}
		<?php
	} 
	?>
	#wc_donation_on_cart .wc-input-text {
		border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
	}

	#wc_donation_on_cart .checkmark {
		border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
	}
	#wc_donation_on_cart .wc-label-radio input:checked ~ .checkmark {
		background-color: #<?php esc_html_e( $donation_button_color); ?>;
	}
	#wc_donation_on_cart .wc-label-radio .checkmark:after {
		border-color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
	}
	#wc_donation_on_cart .wc-label-button {
		border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		color: #<?php esc_html_e( $donation_button_color ); ?>!important;
	}
	#wc_donation_on_cart label.wc-label-button.wc-active {
		background-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
	}
	#wc_donation_on_cart .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
		background: #<?php esc_html_e( $progressBarColor ); ?>;
	}
</style>
