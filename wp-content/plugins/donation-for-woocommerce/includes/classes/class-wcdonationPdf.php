<?php
/**
 * File to  define settings .
 *
 * @package donation
 */

// Include the main Dompdf library (search for autoload path).
require_once WC_DONATION_PATH . 'vendor/autoload.php';

// Reference the Dompdf namespace
use Dompdf\Dompdf;

/**
 *  Class   WcdonationPdf .
 *  Add plugin settings .
 */
class WcdonationPdf {

	private $wc_donation_pdf = null;

	/**
	 * Add plugin menu page .
	 */
	public function __construct() {		

		$this->wc_donation_pdf = new DOMPDF();

		// (Optional) Setup the paper size and orientation
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$this->wc_donation_pdf->setPaper('A4', apply_filters( 'wc_donation_pdf_paper_orientation', 'landscape' ) );

		// set default html5 paresr enabled to true
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$this->wc_donation_pdf->set_option( 'enable_html5_parser', apply_filters( 'wc_donation_pdf_enable_html5_parser', true ) );

		// set default remote images enabled to true
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$this->wc_donation_pdf->set_option( 'isRemoteEnabled', apply_filters( 'wc_donation_pdf_enable_remote_parser', true ) );

		// set default dejavu sans font
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$this->wc_donation_pdf->set_option( 'defaultFont', apply_filters( 'wc_donation_pdf_default_pdf_font', 'dejavu sans' ) );

		// $this->wc_donation_pdf_receipt();

		// print_r( $this->wc_donation_pdf );

	}

	private function _create_pdf_name( $my_order = '' ) {
		
		//Create name of PDF
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$site_name = str_replace( ' ', '_', apply_filters( 'wc_donation_change_pdf_name', get_bloginfo( 'name' ) . '_wc_donation_' ) );
		if ( is_object( $my_order ) ) {
			$file_name = $site_name . substr( md5( $my_order->get_id() ), 5 ) . '.pdf';
		} else {
			$file_name = $site_name . 'reports.pdf';
		}

		return $file_name;
	}

	public function bulk_reports_download_pdf ( $report_ids = array(), $user_id = '' ) {		

		if ( ! empty( $user_id ) ) {
			$args = array(
				'post_type'  => 'wc-donation-report',
				'numberposts' => -1,
				'fields' => 'ids',
				'post_status' => 'private',
				'orderby'    => 'date',
				'sort_order' => 'desc',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'user_id',
						'value'   => $user_id,
						'compare' => '=',
					)
				)
			);

			$report_ids = get_posts( $args );
		}

		if ( is_array( $report_ids ) && count( $report_ids ) > 0 ) {

			ob_start();			
			if ( file_exists( get_stylesheet_directory() . '/donation/views/report_bulk_pdf.php' ) ) {
				include get_stylesheet_directory() . '/donation/views/report_bulk_pdf.php';
			} else {
				include WC_DONATION_PATH . 'includes/views/admin/report_bulk_pdf.php';
			}
			$output = ob_get_clean();
			$this->wc_donation_pdf->loadHtml( $output, 'UTF-8' );
			$file_name = $this->_create_pdf_name();
			// Render the HTML as PDF
			$this->wc_donation_pdf->render();
			// Stream PDF
			$this->wc_donation_pdf->stream( $file_name );
		}
	}

	public function wc_donation_pdf_download( $my_order = '', $ex_product_id = '', $single = false ) {

		if ( ! is_object( $my_order ) ) {
			return;
		}

		ob_start();
		if ( file_exists( get_stylesheet_directory() . '/donation/views/report_single_pdf.php' ) ) {
			include get_stylesheet_directory() . '/donation/views/report_single_pdf.php';
		} else {
			include WC_DONATION_PATH . 'includes/views/admin/report_single_pdf.php';
		}		
		$output = ob_get_clean();
		// loading html for pdf
		$this->wc_donation_pdf->loadHtml( $output, 'UTF-8' );

		$file_name = $this->_create_pdf_name( $my_order );
		// Render the HTML as PDF
		$this->wc_donation_pdf->render();
		// Stream PDF
		$this->wc_donation_pdf->stream( $file_name );
		die();
	}

	public function wc_donation_pdf_receipt( $my_order = '', $ex_product_id = '', $single = false ) {

		if ( ! is_object( $my_order ) ) {
			return;
		}

		ob_start();
		if ( file_exists( get_stylesheet_directory() . '/donation/views/report_single_pdf.php' ) ) {
			include get_stylesheet_directory() . '/donation/views/report_single_pdf.php';
		} else {
			include WC_DONATION_PATH . 'includes/views/admin/report_single_pdf.php';
		}
		$output = ob_get_clean();
		// loading html for pdf
		$this->wc_donation_pdf->loadHtml( $output, 'UTF-8' );

		//Close and output PDF document
		$file_name = $this->_create_pdf_name( $my_order );
		$path      = WC_DONATION_PATH . 'includes/views/pdf-files/';
		
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}

		$output_file = $path . $file_name;

		// Render the HTML as PDF
		$this->wc_donation_pdf->render();
		// Output the generated PDF to Browser
		$output = $this->wc_donation_pdf->output();
		file_put_contents( $output_file, $output );

		return $output_file;
	}

}
