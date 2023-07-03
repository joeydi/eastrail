<?php

class WcdonationWidgetProces extends WP_Widget {
	
	public function __construct() {
		
		add_action( 'widgets_init', array( $this, 'register_wc_donation_widget') );

		parent::__construct(
 
			// Base ID of your widget
			'wc-donation-widget', 
			 
			// Widget name will appear in UI
			__('WC Donation', 'wc-donation'), 
			 
			// Widget description
			array( 'description' => __( 'WC Donation Widget is use to collect donation.', 'wc-donation' ) ) 
		);

	}

	public function register_wc_donation_widget () {

		register_widget( 'WcdonationWidgetProces' );

	}

	// Widget Backend
	public function form ( $instance ) { 

		$campaigns = get_posts(array(
			'fields'          => 'ids',
			'posts_per_page'  => -1,
			'post_type' => 'wc-donation'
		));

		if ( isset( $instance[ 'campaign' ] ) ) {

			$camp = $instance[ 'campaign' ];

		}

		if ( isset( $instance[ 'title' ] ) ) {

			$title = $instance[ 'title' ];

		} else {

			$title = __( 'New title', 'wc-donation' );
			
		}

		// Widget admin form
		?>
		<p>
			<label for="<?php esc_attr_e($this->get_field_id( 'title' )); ?>"><?php esc_html_e( 'Widget Title:' ); ?></label> 
			<input class="widefat" id="<?php esc_attr_e($this->get_field_id( 'title' )); ?>" name="<?php esc_html_e($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php esc_attr_e($this->get_field_id( 'campaign' )); ?>"><?php esc_html_e( 'Select Campaign:' ); ?></label>
			<select name="<?php esc_html_e($this->get_field_name( 'campaign' )); ?>" id="<?php esc_html_e($this->get_field_name( 'campaign' )); ?>">
				<option><?php echo esc_html(__('Select Campaign', 'wc-donation')); ?></option>
				<?php
				foreach ( $campaigns as $campaign ) {
					echo '<option value="' . esc_attr( $campaign ) . '"' .
					selected( esc_attr( $camp ), $campaign ) . '>' .
					esc_attr( get_the_title($campaign) ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update ( $new_instance, $old_instance ) {

		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['campaign'] = ( ! empty( $new_instance['campaign'] ) ) ? strip_tags( $new_instance['campaign'] ) : '';
		return $instance;
	}

	// Creating widget front-end
	public function widget ( $args, $instance ) {

		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$title = isset($instance[ 'title' ]) ? apply_filters( 'widget_title', $instance[ 'title' ] ) : '';
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$id = isset($instance[ 'campaign' ]) ? apply_filters( 'widget_campaign', $instance[ 'campaign' ] ) : '';
		
		$allowed_html = array (
			'div' => array(
				'id' => array(),
				'class' => array()
			),
			'span' => array(
				'id' => array(),
				'class' => array()
			),
			'a' => array(
				'id' => array(),
				'class' => array(),
				'href' => array(),
				'target' => array()
			),
		);

		// before and after widget arguments are defined by themes
		echo wp_kses( $args['before_widget'], $allowed_html );
			
		if ( ! empty( $title ) ) {
			echo wp_kses( $args['before_title'], $allowed_html ) . esc_html( $title ) . wp_kses( $args['after_title'], $allowed_html );
		}
		
		//checking backward compatibility
		$old_product_id = get_option( 'wc-donation-widget-product');
		if ( empty($id) && !empty($old_product_id) ) {			
			
			$id = get_post_meta($old_product_id, 'wc_donation_campaign', true);
		}

		if ( ! empty( $id ) ) {
			
			$post_exist = get_post( $id );
			
			if ( !empty($post_exist) && ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
				$campaign_id = $id;
				$object = WcdonationCampaignSetting::get_product_by_campaign($campaign_id);
				$type = 'widget';
				echo '<div id="wc_donation_on_widget_' . esc_html($campaign_id) . '">';
				/**
				* Action.
				* 
				* @since 3.4.5
				*/
				do_action ('wc_donation_before_widget_add_donation', $campaign_id);
				require WC_DONATION_PATH . '/includes/views/frontend/frontend-order-donation.php';
				echo '</div>';
				/**
				* Action.
				* 
				* @since 3.4.5
				*/
				do_action ('wc_donation_after_widget_add_donation', $campaign_id);
				echo wp_kses( $args['after_widget'], $allowed_html );
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				return;
			}
		}
	}
}

new WcdonationWidgetProces();
