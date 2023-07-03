<?php
class WcDonationReports {

	public function __construct() {

		add_action( 'init', array( $this, 'wc_donation_report_posttype' ) );
		add_filter( 'manage_wc-donation-report_posts_columns', array( $this, 'reports_modify_column_names' ) );
		add_action( 'manage_wc-donation-report_posts_custom_column', array( $this, 'reports_add_custom_column' ), 10, 2 );

		add_filter( 'bulk_actions-edit-wc-donation-report', array( $this, 'remove_from_bulk_actions' ) );

		// if you do not want to remove default "by month filter", remove/comment this line
		add_filter( 'months_dropdown_results', array($this, 'remove_month_dropdown_wc_donation_reports'), 99, 2 );

		// include CSS/JS, in our case jQuery UI datepicker
		add_action( 'admin_enqueue_scripts', array( $this, 'wc_donation_reports_jqueryui' ) );

		// HTML of the filter
		add_action( 'restrict_manage_posts', array( $this, 'report_filter_form' ) );

		// add_filter('views_edit-wc-donation-report', array( $this, 'add_exports_button' ), 99 );

		// the function that filters posts
		add_action( 'pre_get_posts', array( $this, 'filterquery' ) );

		add_action( 'wp_ajax_wc_donation_generate_report_pdf', array( $this, 'wc_donation_generate_report_pdf' ) );
		add_action( 'wp_ajax_nopriv_wc_donation_generate_report_pdf', array( $this, 'wc_donation_generate_report_pdf' ) );

		add_action( 'wp_ajax_wc_donation_send_report_email', array( $this, 'wc_donation_send_report_email' ) );
		add_action( 'wp_ajax_nopriv_wc_donation_send_report_email', array( $this, 'wc_donation_send_report_email' ) );

		add_action( 'admin_init', array( $this, 'wc_donation_report_export_pdf' ) );
		add_action( 'init', array( $this, 'wc_donation_report_export_pdf_user' ) );

		add_action( 'admin_init', array( $this, 'wc_donation_report_export_csv' ) );
		add_action( 'init', array( $this, 'wc_donation_report_export_csv_user' ) );

		add_action( 'admin_notices', array( $this, 'show_notice_for_email_sent_to_client' ) );

		add_action( 'init', array( $this, 'register_user_donation_reports_shortcode' ) );

		add_filter( 'wp_untrash_post_status', array( $this, 'wc_donation_report_change_status_from_trash' ), 99, 3 );

	}

	public function remove_month_dropdown_wc_donation_reports( $months, $post_type ) {

		if ( 'wc-donation-report' === $post_type ) {
			return array();
		}

		return $months;
	}

	public function wc_donation_report_change_status_from_trash( $new_status, $post_id, $previous_status ) {

		if ( 'wc-donation-report' === get_post_type( $post_id ) && 'draft' === $new_status ) {
			
			$new_status = 'private';

			return $new_status;
		}
	}

	public function register_user_donation_reports_shortcode() {

		add_shortcode( 'donation_reports', array( $this, 'render_wc_donation_reports' ) );
		add_shortcode( 'Donation_Reports', array( $this, 'render_wc_donation_reports' ) );

	}

	public function render_wc_donation_reports( $atts ) {

		ob_start();
		if ( ! is_admin() && is_user_logged_in() ) {
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css', array(), '3.0.0' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			echo '<script>
			jQuery( function($) {
				var from = $(\'input[name="DateFrom"]\'),
					to = $(\'input[name="DateTo"]\');				

				$( \'input[name="DateFrom"], input[name="DateTo"]\' ).datepicker( {dateFormat : "yy-mm-dd"} );
		
					// the rest part of the script prevents from choosing incorrect date interval
					from.on( \'change\', function() {
					to.datepicker( \'option\', \'minDate\', from.val() );
				});
					
				to.on( \'change\', function() {
					from.datepicker( \'option\', \'maxDate\', to.val() );
				});
				
			});
			</script>';
			require WC_DONATION_PATH . '/includes/views/frontend/frontend-donation-reports.php';
		} else {
			// translators: %1$s <p>, %2$s </p>
			echo sprintf( esc_html__( '%1$s You need to login in order to see donation reports. %2$s', 'wc-donation' ), '<p>', '</p>' );
		}
		return ob_get_clean();
	}

	public function wc_donation_report_export_csv() {

		if ( isset( $_REQUEST['export_csv'] ) && ! empty( $_REQUEST['export_csv'] ) && isset( $_REQUEST['post'] ) ) {

			$data = $_REQUEST;
			if ( is_array( $data['post'] ) && count( $data['post'] ) > 0 ) {

				// open the file "demosaved.csv" for writing
				/**
				* Filter.
				* 
				* @since 3.4.5
				*/
				$site_name = str_replace( ' ', '_', apply_filters( 'wc_donation_change_csv_name', get_bloginfo( 'name' ) . '_wc_donation_' ) );
				
				$file_name = $site_name . 'reports.csv';

				$file_path_csv = WC_DONATION_PATH . 'reports.csv';

				if ( ! is_file( $file_path_csv ) ) {

					file_put_contents( $file_path_csv, '' );

				}

				// $output = fopen(  'reports.csv', 'r+w' );
				$output = fopen(  $file_path_csv, 'r+w' );

				$header_column = array(
					esc_html__( 'Campaign', 'wc-donation' ),
					esc_html__( 'Currency', 'wc-donation' ),
					esc_html__( 'Amount', 'wc-donation' ),
					esc_html__( 'Order ID', 'wc-donation' ),
					esc_html__( 'Causes', 'wc-donation' ),
					esc_html__( 'Gift Aid', 'wc-donation' ),
					esc_html__( 'Tributes', 'wc-donation' ),
					esc_html__( 'Date', 'wc-donation' ),
					esc_html__( 'Payment Method', 'wc-donation' ),
				);

				// save the column headers
				fputcsv( $output, $header_column );

				// $result = array();

				foreach ( $data['post'] as $report_id ) {
					$order_id   = get_post_meta( $report_id, 'order_id', true );
					$product_id = get_post_meta( $report_id, 'product_id', true );
					$order      = wc_get_order( $order_id );
					if ( is_object( $order ) ) {
						$currency = $order->get_currency();
						foreach ( $order->get_items() as $item_id => $item ) {
							$type = get_post_meta( $item->get_product_id(), 'is_wc_donation', true );
							if ( ! empty( $type ) && 'donation' == $type && $item->get_product_id() == $product_id ) {
								$campaign_id   = wc_get_order_item_meta( $item_id, 'campaign_id', true );
								$cause         = wc_get_order_item_meta( $item_id, 'cause_name', true );
								$gift_aid      = wc_get_order_item_meta( $item_id, 'gift_aid', true );
								$tribute       = wc_get_order_item_meta( $item_id, 'tribute', true );
								$campaign_name = get_the_title( $campaign_id );
								$item_total    = $item->get_total();
								$created_date  = $order->get_date_created();
								$payment       = $order->get_payment_method_title();

								// put each details in a row in csv file.
								fputcsv( $output, array( $campaign_name, $currency, $item_total, $order_id, $cause, $gift_aid, $tribute, $created_date, $payment ) );
							}
						}
					}
				}

				// reset the file pointer to the start of the file
				fseek( $output, 0 );

				// tell the browser it's going to be a csv file
				header( 'Content-Encoding: UTF-8' );
				header( 'Content-type: text/csv; charset=UTF-8' );

				// tell the browser we want to save it instead of displaying it
				header( 'Content-Disposition: attachment; filename="' . $file_name . '";' );

				// make php send the generated csv lines to the browser
				fpassthru( $output );

				// Close the file
				fclose( $output );

				exit;

			}
		}
	}

	public function wc_donation_report_export_csv_user() {

		if ( isset( $_REQUEST['export_csv'] ) && ! empty( $_REQUEST['export_csv'] ) && isset( $_REQUEST['user_id'] ) ) {

			if ( ! empty( sanitize_text_field( $_REQUEST['user_id'] ) ) ) {
				$args = array(
					'post_type'   => 'wc-donation-report',
					'numberposts' => -1,
					'fields'      => 'ids',
					'post_status' => 'private',
					'orderby'     => 'date',
					'sort_order'  => 'desc',
					'meta_query'  => array(
						'relation' => 'AND',
						array(
							'key'     => 'user_id',
							'value'   => sanitize_text_field( $_REQUEST['user_id'] ),
							'compare' => '=',
						),
					),
				);

				$report_ids = get_posts( $args );
			}

			if ( is_array( $report_ids ) && count( $report_ids ) > 0 ) {

				// open the file "demosaved.csv" for writing
				/**
				* Filter.
				* 
				* @since 3.4.5
				*/
				$site_name = str_replace( ' ', '_', apply_filters( 'wc_donation_change_csv_name', get_bloginfo( 'name' ) . '_wc_donation_' ) );
				$file_name = $site_name . 'reports.csv';

				$file_path_csv = WC_DONATION_PATH . 'reports.csv';

				if ( ! is_file( $file_path_csv ) ) {

					file_put_contents( $file_path_csv, '' );

				}

				$output = fopen(  'reports.csv', 'r+w' );

				$header_column = array(
					esc_html__( 'Campaign', 'wc-donation' ),
					esc_html__( 'Currency', 'wc-donation' ),
					esc_html__( 'Amount', 'wc-donation' ),
					esc_html__( 'Order ID', 'wc-donation' ),
					esc_html__( 'Causes', 'wc-donation' ),
					esc_html__( 'Gift Aid', 'wc-donation' ),
					esc_html__( 'Tributes', 'wc-donation' ),
					esc_html__( 'Date', 'wc-donation' ),
					esc_html__( 'Payment Method', 'wc-donation' ),
				);

				// save the column headers
				fputcsv( $output, $header_column );

				// $result = array();

				foreach ( $report_ids as $report_id ) {
					$order_id   = get_post_meta( $report_id, 'order_id', true );
					$product_id = get_post_meta( $report_id, 'product_id', true );
					$order      = wc_get_order( $order_id );
					if ( is_object( $order ) ) {
						$currency = $order->get_currency();
						foreach ( $order->get_items() as $item_id => $item ) {
							$type = get_post_meta( $item->get_product_id(), 'is_wc_donation', true );
							if ( ! empty( $type ) && 'donation' == $type && $item->get_product_id() == $product_id ) {
								$campaign_id   = wc_get_order_item_meta( $item_id, 'campaign_id', true );
								$cause         = wc_get_order_item_meta( $item_id, 'cause_name', true );
								$gift_aid      = wc_get_order_item_meta( $item_id, 'gift_aid', true );
								$tribute       = wc_get_order_item_meta( $item_id, 'tribute', true );
								$campaign_name = get_the_title( $campaign_id );
								$item_total    = $item->get_total();
								$created_date  = $order->get_date_created();
								$payment       = $order->get_payment_method_title();

								// put each details in a row in csv file.
								fputcsv( $output, array( $campaign_name, $currency, $item_total, $order_id, $cause, $gift_aid, $tribute, $created_date, $payment ) );
							}
						}
					}
				}

				// reset the file pointer to the start of the file
				// fseek( $output, 0 );

				// tell the browser it's going to be a csv file
				header( 'Content-Encoding: UTF-8' );
				header( 'Content-type: text/csv; charset=UTF-8' );

				// tell the browser we want to save it instead of displaying it
				header( 'Content-Disposition: attachment; filename="' . $file_name . '";' );

				// make php send the generated csv lines to the browser
				fpassthru( $output );

				// Close the file
				fclose( $output );

				exit;

			}
		}
	}

	public function wc_donation_report_export_pdf() {

		if ( isset( $_REQUEST['export_pdf'] ) && ! empty( $_REQUEST['export_pdf'] ) && isset( $_REQUEST['post'] ) ) {

			$data = $_REQUEST;
			$pdf  = new WcdonationPdf();
			$pdf->bulk_reports_download_pdf( $data['post'], '' );
		}
	}


	public function wc_donation_report_export_pdf_user() {
		if ( isset( $_REQUEST['export_pdf'] ) && ! empty( $_REQUEST['export_pdf'] ) && isset( $_REQUEST['user_id'] ) ) {

			$pdf = new WcdonationPdf();
			$pdf->bulk_reports_download_pdf( '', sanitize_text_field( $_REQUEST['user_id'] ) );

		}
	}

	public function show_notice_for_email_sent_to_client() {
		if ( isset( $_REQUEST['email_sent'] ) && 1 == sanitize_text_field( $_REQUEST['email_sent'] ) ) {
			?>
			<div id="message" class="notice notice-success">
				<p><?php printf( esc_html( __( 'Email sent to client successfully', 'wc-donation' ) ) ); ?></p>
			</div>
			<?php
		}

		if ( isset( $_REQUEST['email_sent'] ) && 0 == sanitize_text_field( $_REQUEST['email_sent'] ) ) {
			?>
			<div id="message" class="notice notice-success">
				<p><?php printf( esc_html( __( 'There is an error while sending email to client. Please ask Administrator.', 'wc-donation' ) ) ); ?></p>
			</div>
			<?php
		}

	}

	public function wc_donation_send_report_email() {

		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field($_REQUEST['_wpnonce']), sanitize_text_field($_REQUEST['action']) ) ) {

			if ( isset( $_REQUEST['order_id'] ) ) {
				$my_order                    = wc_get_order( sanitize_text_field( $_REQUEST['order_id'] ) );
			}

			if ( isset( $_REQUEST['product_id'] ) ) {
				$product_id               = sanitize_text_field( $_REQUEST['product_id'] );
			}

			$email                    = $my_order->get_billing_email();
			$headers                  = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . esc_attr( get_bloginfo( 'name' ) ) . ' <' . esc_attr( get_option( 'admin_email' ) ) . '>' );
			$order_billing_first_name = $my_order->get_billing_first_name();
			/**
			* Filter.
			* 
			* @since 3.4.5
			*/
			$subject                  = apply_filters( 'wc_donation_change_admin_email_subject', __( 'WC Donation Report for Order #' . sanitize_text_field( $_REQUEST['order_id'] ), 'wc-donation' ) );
			ob_start();			
			if ( file_exists( get_stylesheet_directory() . '/donation/views/report_email.php' ) ) {
				include get_stylesheet_directory() . '/donation/views/report_email.php';
			} else {
				include WC_DONATION_PATH . 'includes/views/admin/report_email.php';
			}
			$message = ob_get_clean();

			$pdf      = new WcdonationPdf();
			$pdf_path = $pdf->wc_donation_pdf_receipt( $my_order, $product_id, true );
			if ( ! empty( $pdf_path ) ) {
				$attachments[] = $pdf_path;
			}

			if ( ! empty( $email ) ) {
				$success = wp_mail( $email, $subject, $message, $headers, $attachments );
				if ( $success ) {
					wp_redirect( admin_url( 'edit.php?post_type=wc-donation-report&email_sent=1' ) );
				} else {
					wp_redirect( admin_url( 'edit.php?post_type=wc-donation-report&email_sent=0' ) );
				}

				exit;
			}
		}

		wp_die();
	}

	public function wc_donation_generate_report_pdf() {

		if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field($_REQUEST['_wpnonce']), sanitize_text_field($_REQUEST['action']) ) ) {

			if ( isset( $_REQUEST['order_id'] ) ) {
				$order = wc_get_order( sanitize_text_field( $_REQUEST['order_id'] ) );
			}

			if ( isset( $_REQUEST['product_id'] ) ) {
				$pdf = new WcdonationPdf();
				$pdf->wc_donation_pdf_download( $order, sanitize_text_field( $_REQUEST['product_id'] ), true );
			}
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=wc-donation-report' ) );
		}

		wp_die();
	}

	public function remove_from_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	public function reports_modify_column_names( $columns ) {

		unset( $columns['title'] );
		unset( $columns['date'] );
		
		$columns['campaign_name']   = __( 'Campaign', 'wc-donation' );
		$columns['product_sku']     = __( 'SKU', 'wc-donation' );
		$columns['cause_name']      = __( 'Cause', 'wc-donation' );
		$columns['tribute']         = __( 'Tribute', 'wc-donation' );
		$columns['gift_aid']        = __( 'Gift Aid', 'wc-donation' );
		$columns['order_id']        = __( 'Order No.', 'wc-donation' );		
		$columns['donation_amount'] = __( 'Amount', 'wc-donation' );
		$columns['action']          = __( 'Actions', 'wc-donation' );
		$columns['date']            = __( 'Date', 'wc-donation' );

		/**				 
		 * WC Donation filter.
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'wc_add_reports_columns', $columns );
	}

	public function reports_add_custom_column( $column, $ID ) {

		if ( 'campaign_name' == $column ) {
			$campaign_id = get_post_meta( $ID, 'campaign_id', true );
			echo '<a href="' . esc_url( get_edit_post_link( $campaign_id ) ) . '" target="_blank">' . esc_html( get_the_title( $campaign_id ) ) . '</a>';
		}

		if ( 'product_sku' == $column ) {
			$product_id = get_post_meta( $ID, 'product_id', true );
			$product    = wc_get_product( $product_id );
			echo esc_attr( $product->get_sku() );
		}

		if ( 'cause_name' == $column ) {
			$cause_name = get_post_meta( $ID, 'cause_name', true );
			if ( ! empty( $cause_name ) ) {
				echo esc_attr( $cause_name );
			} else {
				echo '-';
			}
		}

		if ( 'tribute' == $column ) {
			$tribute = get_post_meta( $ID, 'tribute', true );
			if ( ! empty( $tribute ) ) {
				echo esc_attr( $tribute );
			} else {
				echo '-';
			}
		}

		if ( 'gift_aid' == $column ) {
			$gift_aid = get_post_meta( $ID, 'gift_aid', true );
			if ( 'yes' == $gift_aid ) {
				echo esc_attr( $gift_aid );
			} else {
				echo '-';
			}
		}

		if ( 'order_id' == $column ) {
			$order_id = get_post_meta( $ID, 'order_id', true );
			echo '<a href="' . esc_url( get_edit_post_link( $order_id ) ) . '" target="_blank">#' . esc_html( $order_id ) . '</a>';
			// echo get_post_meta( $ID, 'user_id', true );
		}

		if ( 'donation_amount' == $column ) {
			$donation_amount = get_post_meta( $ID, 'donation_amount', true );
			$currency        = get_post_meta( $ID, 'currency', true );
			echo esc_attr( $currency . $donation_amount );
		}

		if ( 'action' == $column ) {
			$order_id   = get_post_meta( $ID, 'order_id', true );
			$product_id = get_post_meta( $ID, 'product_id', true );
			echo '<div class="report-action"><a href="' . esc_url( admin_url( 'admin-ajax.php' ) . '?action=wc_donation_send_report_email&order_id=' . $order_id . '&product_id=' . $product_id . '&_wpnonce=' . wp_create_nonce( 'wc_donation_send_report_email' ) ) . '" ><img src="' . esc_url( WC_DONATION_URL . 'assets/images/email.png' ) . '" title="Send Email" /></a>
			<span class="sep">|</span>
			<a href="' . esc_url( admin_url( 'admin-ajax.php' ) . '?action=wc_donation_generate_report_pdf&order_id=' . $order_id . '&product_id=' . $product_id . '&_wpnonce=' . wp_create_nonce( 'wc_donation_generate_report_pdf' ) ) . '" ><img src="' . esc_url( WC_DONATION_URL . 'assets/images/download-pdf.png' ) . '" title="Download PDF" /></a></div>';
		}

		/**				 
		 * WC Donation action.
		 * 
		 * @since 1.0.0
		 */
		do_action( 'wc_add_reports_columns_column', $column, $ID );

	}

	public static function add_report( $order_id, $item_id, $product_id, $donation_amount, $currency ) {

		$campaign_id = wc_get_order_item_meta( $item_id, 'campaign_id', true );
		$cause       = wc_get_order_item_meta( $item_id, 'cause_name', true );
		$gift_aid    = wc_get_order_item_meta( $item_id, 'gift_aid', true );
		$tribute     = wc_get_order_item_meta( $item_id, 'tribute', true );

		$order   = wc_get_order( $order_id );
		$user_id = $order->get_customer_id();

		$report_id = wp_insert_post(
			array(
				'post_type'   => 'wc-donation-report',
				'post_title'  => __( 'Report for ', 'wc-donation' ) . get_the_title( $campaign_id ),
				'post_status' => 'private',
			)
		);

		if ( ! empty( $report_id ) ) {

			update_post_meta( $report_id, 'campaign_id', $campaign_id );
			update_post_meta( $report_id, 'product_id', $product_id );
			update_post_meta( $report_id, 'order_id', $order_id );
			update_post_meta( $report_id, 'user_id', $user_id );
			update_post_meta( $report_id, 'order_item_id', $item_id );
			update_post_meta( $report_id, 'cause_name', $cause );
			update_post_meta( $report_id, 'tribute', $tribute );
			update_post_meta( $report_id, 'gift_aid', $gift_aid );
			update_post_meta( $report_id, 'donation_amount', $donation_amount );
			update_post_meta( $report_id, 'currency', $currency );
		}
	}

	public function wc_donation_report_posttype() {
		$labels = array(
			'name'                  => _x( 'Donation Reports', 'Post type general name', 'wc-donation' ),
			'singular_name'         => _x( 'Donation Reports', 'Post type singular name', 'wc-donation' ),
			'menu_name'             => _x( 'Reports', 'Admin Menu text', 'wc-donation' ),
			'name_admin_bar'        => _x( 'Donation Reports', 'Add New on Toolbar', 'wc-donation' ),
			'all_items'             => __( 'Reports', 'wc-donation' ),
			'search_items'          => __( 'Search Donation Reports', 'wc-donation' ),
			'parent_item_colon'     => __( 'Parent Donation Reports:', 'wc-donation' ),
			'not_found'             => __( 'No Donation Reports found.', 'wc-donation' ),
			'not_found_in_trash'    => __( 'No Donation Reports found in Trash.', 'wc-donation' ),
			'filter_items_list'     => _x( 'Filter WC Donation Reports list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'wc-donation' ),
			'items_list_navigation' => _x( 'WC Donation Reports list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'wc-donation' ),
		);

			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => 'edit.php?post_type=wc-donation',
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'supports'           => false,
			);

			register_post_type( 'wc-donation-report', $args );
	}

	/*
	 * Add jQuery UI CSS and the datepicker script
	 * Everything else should be already included in /wp-admin/ like jquery, jquery-ui-core etc
	 * If you use WooCommerce, you can skip this function completely
	 */
	public function wc_donation_reports_jqueryui() {
		
		global $current_screen;
		
		if ( isset( $current_screen->post_type ) && 'wc-donation-report' == $current_screen->post_type ) {
			wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css', array(), WC_DONATION_VERSION );
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}
	}

	/* We are not using this function */
	public function add_exports_button( $views ) {
		$views['export_pdf'] = '<a id="wc-donation-export-report-pdf" href="' . admin_url( 'admin-ajax.php' ) . '?action=wc_donation_report_export_pdf&_wpnonce=' . wp_create_nonce( 'wc_donation_report_export_pdf' ) . '">Download PDF Reports</a>';
		$views['export_csv'] = '<a id="wc-donation-export-report-csv" href="' . admin_url( 'admin-ajax.php' ) . '?action=wc_donation_report_export_csv&_wpnonce=' . wp_create_nonce( 'wc_donation_report_export_csv' ) . '">Download CSV Reports</a>';
		return $views;
	}
	/* We are not using this function */

	/*
	 * Two input fields with CSS/JS
	 * If you would like to move CSS and JavaScript to the external file - welcome.
	 */
	public function report_filter_form( $post_type ) {

		if ( 'wc-donation-report' == $post_type ) {
			$from = ( isset( $_GET['DateFrom'] ) && sanitize_text_field( $_GET['DateFrom'] ) ) ? sanitize_text_field( $_GET['DateFrom'] ) : '';
			$to   = ( isset( $_GET['DateTo'] ) && sanitize_text_field( $_GET['DateTo'] ) ) ? sanitize_text_field( $_GET['DateTo'] ) : '';
			// $preset = ( isset( $_GET['PresetDate'] ) && sanitize_text_field( $_GET['PresetDate'] ) ) ? sanitize_text_field( $_GET['PresetDate'] ) : '';

			echo '
			<input id="wc-donation-export-report-pdf" class="button action" type="submit" name="export_pdf" value="Download PDF Reports" >

			<input id="wc-donation-export-report-csv" class="button action" type="submit" name="export_csv" value="Download CSV Reports" >

			<select name="PresetDate">
				<option value="">--Select Presets--</option>
				<option value="today" ' . selected( @$preset, 'today', false ) . ' >Today</option>
				<option value="yesterday" ' . selected( @$preset, 'yesterday', false ) . ' >Yesterday</option>
				<option value="last_week" ' . selected( @$preset, 'last_week', false ) . ' >Last 7 days</option>
				<option value="last_month" ' . selected( @$preset, 'last_month', false ) . ' >Last Month</option>
				<option value="last_year" ' . selected( @$preset, 'last_year', false ) . ' >Last Year</option>
			</select>

			<input type="text" name="DateFrom" placeholder="Date From" value="' . esc_attr( $from ) . '" autocomplete="off" readonly />
			<input type="text" name="DateTo" placeholder="Date To" value="' . esc_attr( $to ) . '" autocomplete="off" readonly />	
		
			<script>
			jQuery( function($) {
				var from = $(\'input[name="DateFrom"]\'),
					to = $(\'input[name="DateTo"]\');				

				$( \'input[name="DateFrom"], input[name="DateTo"]\' ).datepicker( {dateFormat : "yy-mm-dd"} );
		
					// the rest part of the script prevents from choosing incorrect date interval
					from.on( \'change\', function() {
					to.datepicker( \'option\', \'minDate\', from.val() );
				});
					
				to.on( \'change\', function() {
					from.datepicker( \'option\', \'maxDate\', to.val() );
				});

				$( \'select[name="PresetDate"]\' ).on( \'change\', function() {					
					$(\'#post-query-submit\').click();
				});
				
			});
			</script>';
		}
	}

	/*
	 * The main function that actually filters the posts
	 */
	public function filterquery( $admin_query ) {
		global $pagenow;

		// && in_array( $pagenow, array( 'edit.php' ) )
		if ( is_admin() && isset( $_GET['post_type'] ) && 'wc-donation-report' == sanitize_text_field( $_GET['post_type'] ) && $admin_query->is_main_query() ) {

			// 'day'  => 29,
			// 'month' => 07,
			// 'year' => 2021,

			if ( ( isset( $_GET['DateFrom'] ) && ! empty( sanitize_text_field( $_GET['DateFrom'] ) ) ) || ( isset( $_GET['DateTo'] ) && ! empty( sanitize_text_field( $_GET['DateTo'] ) ) ) ) {

				$data = array(
					'after'     => sanitize_text_field( $_GET['DateFrom'] ), // any strtotime()-acceptable format!
					'before'    => sanitize_text_field( $_GET['DateTo'] ),
					'inclusive' => true, // include the selected days as well
					'column'    => 'post_date', // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
				);
			}

			if ( isset( $_GET['PresetDate'] ) && ! empty( sanitize_text_field( $_GET['PresetDate'] ) ) ) {

				if ( 'today' == sanitize_text_field( $_GET['PresetDate'] ) ) {

					// $today = getdate('yesterday');
					$today = gmdate( 'Y-m-d', strtotime( sanitize_text_field( $_GET['PresetDate'] ) ) );
					$today = explode( '-', $today );

					// echo sanitize_text_field( $_GET['PresetDate'] );
					// print_r( $today );
					// die();

					$data = array(
						'year'  => $today[0],
						'month' => $today[1],
						'day'   => $today[2],
					);

				} elseif ( 'yesterday' == sanitize_text_field( $_GET['PresetDate'] ) ) {

					$yesterday = gmdate( 'Y-m-d', strtotime( sanitize_text_field( $_GET['PresetDate'] ) ) );
					$yesterday = explode( '-', $yesterday );

					$data = array(
						'year'  => $yesterday[0],
						'month' => $yesterday[1],
						'day'   => $yesterday[2],
					);

				} elseif ( 'last_week' == sanitize_text_field( $_GET['PresetDate'] ) ) {

					$last_week_start_date = gmdate( 'Y-m-d', strtotime( '7 days ago' ) );
					$last_week_end_date   = gmdate( 'Y-m-d', strtotime( '1 days ago' ) );					

					$data = array(
						'after'     => $last_week_start_date, // any strtotime()-acceptable format!
						'before'    => $last_week_end_date,
						'inclusive' => true, // include the selected days as well
						'column'    => 'post_date', // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
					);

				} elseif ( 'last_month' == sanitize_text_field( $_GET['PresetDate'] ) ) {

					$last_month = gmdate( 'Y-m-d', strtotime( 'last month' ) );
					$last_month = explode( '-', $last_month );					

					$data = array(
						'year'  => $last_month[0],
						'month' => $last_month[1],
					);

				} elseif ( 'last_year' == sanitize_text_field( $_GET['PresetDate'] ) ) {

					$last_year = gmdate( 'Y-m-d', strtotime( 'last year' ) );
					$last_year = explode( '-', $last_year );

					$data = array(
						'year' => $last_year[0],
					);
				}
			}

			if ( isset( $data ) ) {
				$admin_query->set(
					'date_query', // I love date_query appeared in WordPress 3.7!
					$data
				);
			}
		}

		return $admin_query;

	}

}

new WcDonationReports();
