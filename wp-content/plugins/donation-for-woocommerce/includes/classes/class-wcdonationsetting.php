<?php
/**
 * File to  define settings .
 *
 * @package donation
 */

/**
 *  Class   WcdonationSetting .
 *  Add plugin settings .
 */
class WcdonationSetting {

	/**
	 * Plugin page slug .
	 *
	 * @var type
	 */
	private $plugin_page_slug;

	/**
	 * Add plugin menu page .
	 */
	public function __construct() {
		
		add_action( 'admin_menu', array( $this, 'create_sub_menu' ) );
		add_action( 'init', array( $this, 'wc_donation_posttype' ) );
		$this->plugin_page_slug = 'wc-donation-setting';
		
		//On save post
		add_action( 'save_post', array( $this, 'wc_donation_save_post' ), 999, 3 );
		add_action( 'pre_get_posts', array( $this, 'get_products_without_donation') );

		add_filter('manage_wc-donation_posts_columns', array( $this, 'wc_donation_modify_column_names') );
		add_action('manage_wc-donation_posts_custom_column', array( $this, 'wc_donation_add_custom_column'), 9, 2);

		add_action( 'wp_ajax_wc_donation_sync_data', array( $this, 'wc_donation_sync_data' ) );

		add_action( 'wp_ajax_wc_donation_page_block_sorting', array( $this, 'wc_donation_page_block_sorting' ) );
		
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'wc_cart_item_quantity' ), 99, 3 );

	}

	public function wc_donation_page_block_sorting() {
		
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'donation_reset_nonce' ) ) {
			exit('Unauthorized');
		}

		$post = $_POST;

		if ( isset( $post['order'] ) && isset( $post['campaign_id'] ) ) {
			update_post_meta( $post['campaign_id'], 'ordering', $post['order'] );
		}

		wp_die();
	}

	public function wc_donation_sync_data() {
		
		$result = array();
		$campaign_result = array(
			'campaign_id' => '',
			'campaign_name' => '',
			'total_donars' => '',
			'total_donation_count' => '',
			'total_donation_amount' => '',
			'status' => ''
		);

		$campaign_ids = get_posts( array(
			'post_type' => 'wc-donation',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields' => 'ids'
		) );		

		if ( is_array( $campaign_ids ) && count( $campaign_ids ) > 0 ) {
			foreach ($campaign_ids as $campaign_id ) {

				$campaign_result['campaign_id'] = $campaign_id;
				$campaign_result['campaign_name'] = get_the_title($campaign_id);

				$product_id = get_post_meta( $campaign_id, 'wc_donation_product', true);
				$product = wc_get_product( $product_id );
				if ( $product && ! empty( $product_id ) ) {
					$total_donars = self::get_donation_donors( $product_id );
					$total_donation_count = self::get_donation_total_count( $product_id );
					$total_donation_amount = self::get_donation_total( $product_id );

					$campaign_result['total_donars'] = $total_donars;
					$campaign_result['total_donation_count'] = $total_donation_count;
					$campaign_result['total_donation_amount'] = $total_donation_amount;
					$campaign_result['status'] = 'success';

					array_push( $result, $campaign_result );

				} else {
					
					$campaign_result['total_donars'] = '';
					$campaign_result['total_donation_count'] = '';
					$campaign_result['total_donation_amount'] = '';
					$campaign_result['status'] = 'failed';

					array_push( $result, $campaign_result );

				}
			}
		}

		$stored_data = get_option( 'wc_donation_sync_data', array() );

		if ( is_array( $stored_data ) && isset( $stored_data['counter'] ) ) {
			$counter = $stored_data['counter'] + 1;
		} else {
			$counter = 1;
		}

		update_option( 'wc_donation_sync_data', array( 'update_date' => gmdate('Y-m-d H:i:s a'), 'updated_data' => $result, 'counter' => $counter ) );

		$user_id = get_current_user_id();
		add_user_meta( $user_id, 'wc_donation_plugin_notice_dismissed', 'true', true );

		echo json_encode( $result );
		wp_die();
	}

	public static function get_donation_total_count ( $product_id ) {
		global $wpdb;

		// Find total donation count in the DB order table
		$total_donation_count = $wpdb->get_col($wpdb->prepare( "
		SELECT COUNT(oi.order_id)
		FROM {$wpdb->posts} AS p, {$wpdb->prefix}woocommerce_order_items AS oi, {$wpdb->prefix}woocommerce_order_itemmeta AS oim
		WHERE oi.order_item_id = oim.order_item_id
		AND oi.order_id = p.ID
		AND p.post_status IN ('wc-completed', 'wc-processing')
		AND oim.meta_key = '_product_id'
		AND oim.meta_value = %d
		ORDER BY oi.order_item_id DESC", $product_id
		) );
		if ( isset( $total_donation_count[0] ) ) {
			update_post_meta( $product_id, 'total_donations', $total_donation_count[0]);
		} else {
			update_post_meta( $product_id, 'total_donations', 0);
		}
		return $total_donation_count[0];
	}
	public static function get_donation_donors ( $product_id ) {
		global $wpdb;
		// Find total donors in the DB posts table
		$total_donors = $wpdb->get_col( $wpdb->prepare( "
		SELECT COUNT(DISTINCT pm.meta_value) FROM {$wpdb->posts} AS p
		INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
		INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
		INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
		WHERE p.post_status IN ( 'wc-completed', 'wc-processing' )
		AND pm.meta_key IN ( '_billing_email' )
		AND im.meta_key IN ( '_product_id' )
		AND im.meta_value = %d
		", $product_id ) );
		if ( isset( $total_donors[0] ) ) {
			update_post_meta( $product_id, 'total_donors', $total_donors[0] );
		} else {
			update_post_meta( $product_id, 'total_donors', 0);
		}
		// Print array on screen
		return $total_donors[0];
	}

	public static function get_donation_total ( $product_id ) {
		global $wpdb;

		// Find total doantions in the DB order table
		$total_donation_amount = $wpdb->get_col( $wpdb->prepare( "
		SELECT Sum(order_item_meta__substotal.meta_value) AS product_subtotal
		FROM {$wpdb->posts} AS posts 
		INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items 
		ON posts.id = order_items.order_id 
		INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__substotal 
		ON ( order_items.order_item_id = 
		order_item_meta__substotal.order_item_id ) 
		AND ( order_item_meta__substotal.meta_key = '_line_subtotal' ) 
		INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id 
		ON ( order_items.order_item_id = order_item_meta__product_id.order_item_id ) 
		AND ( order_item_meta__product_id.meta_key = '_product_id' ) 
		INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS 
		order_item_meta__product_id_array 
		ON order_items.order_item_id = order_item_meta__product_id_array.order_item_id 
		WHERE  posts.post_type IN ( 'shop_order' ) 
		AND posts.post_status IN ( 'wc-completed', 'wc-processing' )
		AND (( order_item_meta__product_id_array.meta_key IN ( '_product_id' ) 
		AND order_item_meta__product_id_array.meta_value IN ( %d ) ))
		", $product_id ) );
		// Print array on screen
		if ( isset( $total_donation_amount[0] ) ) {
			update_post_meta( $product_id, 'total_donation_amount', round($total_donation_amount[0], 2) );
		} else {
			update_post_meta( $product_id, 'total_donation_amount', 0);
		}
		return round($total_donation_amount[0], 2);
	}

	public static function has_bought_items( $product_id = 0, $all_columns = ''  ) {
		
		switch ( $all_columns ) {
			case 'total_donors':
				$count['total_donors'] = !empty(get_post_meta( $product_id, 'total_donors', true )) ? get_post_meta( $product_id, 'total_donors', true ) : 0;
				break;

			case 'total_donation_amount':
				$count['total_donation_amount'] = !empty(get_post_meta( $product_id, 'total_donation_amount', true )) ? get_post_meta( $product_id, 'total_donation_amount', true ) : 0;
				break;

			case 'total_donations':
				$count['total_donations'] = !empty(get_post_meta( $product_id, 'total_donations', true )) ? get_post_meta( $product_id, 'total_donations', true ) : 0;
				break;

			default:
				$count['total_donors'] = !empty(get_post_meta( $product_id, 'total_donors', true )) ? get_post_meta( $product_id, 'total_donors', true ) : 0;
				$count['total_donations'] = !empty(get_post_meta( $product_id, 'total_donations', true )) ? get_post_meta( $product_id, 'total_donations', true ) : 0;
				$count['total_donation_amount'] = !empty(get_post_meta( $product_id, 'total_donation_amount', true )) ? get_post_meta( $product_id, 'total_donation_amount', true ) : 0;
		}

		return $count;

	}
	

	public function wc_donation_modify_column_names ( $columns ) {

		unset($columns['title']);
		unset($columns['date']);
		
		$columns['campaign_name'] = __('Campaign', 'wc-donation');		
		$columns['amount'] = __('Amount', 'wc-donation');
		$columns['total_donations'] = __('Total Donations', 'wc-donation');
		$columns['total_donation_amount'] = __('Total Amount', 'wc-donation');
		$columns['initial_goal_amount'] = __('Initial Goal Amount', 'wc-donation');
		$columns['total_donors'] = __('Total Donors', 'wc-donation');
		$columns['shortcode'] = __('Shortcode', 'wc-donation');
		$columns['actions'] = __('Actions', 'wc-donation');
		return $columns;
	}

	public function wc_donation_add_custom_column ( $column, $postId ) {

		$object = WcdonationCampaignSetting::get_product_by_campaign($postId);

		if ( get_woocommerce_currency_symbol() ) {
			$currency_symbol =  get_woocommerce_currency_symbol();
		}

		$donation_min_value = !empty( $object->campaign['freeMinAmount'] ) ? $object->campaign['freeMinAmount'] : 0;
		$donation_max_value = !empty( $object->campaign['freeMaxAmount'] ) ? $object->campaign['freeMaxAmount'] : 1000;
		$donation_values = !empty( $object->campaign['predAmount'] ) ? $object->campaign['predAmount'] : array();

		$amounts = array();
		if ( !empty($object->campaign['amount_display']) && ( 'both' == $object->campaign['amount_display'] || 'free-value' == $object->campaign['amount_display'] ) ) {
			//enter min value in array	
			array_push($amounts, $donation_min_value);
			
			//enter max value in array
			array_push($amounts, $donation_max_value);
		}

		if ( !empty($object->campaign['amount_display']) && ( 'both' == $object->campaign['amount_display'] || 'predefined' == $object->campaign['amount_display'] ) ) {
			// print_r($donation_values[0]);
			if ( !empty($donation_values[0]) ) {
				foreach ( $donation_values[0] as $val ) {
					$amounts[] = $val;
				}
			}

		}
		
		$product_id = get_post_meta($postId, 'wc_donation_product', true);
		$total_donation_amount = 0;
		$total_donors = 0;
		$total_donation_count = 0;		
		if ( in_array( $column, array('total_donations', 'total_donation_amount', 'total_donors') ) ) {
			$get_donations = $this->has_bought_items( $product_id, $column );
		} else {
			$get_donations = array();
		}
		
		if ( isset($get_donations['total_donations']) ) {
			$total_donation_count = $get_donations['total_donations'];
		}
		
		if ( isset($get_donations['total_donation_amount']) ) {
			$total_donation_amount = $get_donations['total_donation_amount'];
		}

		if ( isset($get_donations['total_donors']) ) {
			$total_donors = $get_donations['total_donors'];
		}
		

		switch ($column) {

			case 'campaign_name':
				echo '<h4 class="m-0">' . esc_attr(get_the_title($postId)) . '</h4>';
				break;

			case 'amount':
				if ( is_array( $amounts ) && count( $amounts ) > 0 ) {				
					echo esc_attr($currency_symbol) . esc_attr(min( $amounts )) . ' - ' . esc_attr($currency_symbol) . esc_attr(max( $amounts ));
				} else {
					echo '-';
				}
				break;
			
			case 'total_donations':
				echo esc_attr($total_donation_count);
				break;
		
			case 'total_donation_amount':
				//echo esc_attr($currency_symbol) . esc_attr($total_donation_amount);
				echo wp_kses_post(wc_price( $total_donation_amount ));
				break;

			case 'initial_goal_amount':
				echo wp_kses_post(wc_price( $object->goal['fixed_initial_amount'] ));
				break;

			case 'total_donors':
				echo esc_attr($total_donors);
				break;

			case 'shortcode':
				?>
				<textarea spellcheck="false" id="wc-donation-campaign-shortcode-<?php esc_attr_e($postId); ?>" class="wc-shortcode-field">[wc_woo_donation id="<?php esc_attr_e($postId); ?>"]</textarea>
				<a href="javascript:void(0);" onclick="copyToClip('wc-donation-campaign-shortcode-<?php esc_attr_e($postId); ?>')"><span class="dashicons dashicons-admin-page"></span></a>
				<?php
				break;
			
			case 'actions':
				echo '<a href="' . esc_url(get_edit_post_link( $postId )) . '" class="wc-dashicons editIcon"> <span class="dashicons dashicons-edit"></span> </a>';				
				echo '<a href="' . esc_url(get_preview_post_link($postId)) . '" class="wc-dashicons viewIcon"> <span class="dashicons dashicons-visibility"></span> </a>';
				if ( 'publish' == get_post_status($postId) ) {
					echo '<a href="' . esc_url(get_delete_post_link( $postId )) . '" class="wc-dashicons deleteIcon" title="Delete"> <span class="dashicons dashicons-trash"></span> </a>';
				}

				if ( 'trash' == get_post_status($postId) ) {
					$restore_link = wp_nonce_url(
						"post.php?action=untrash&amp;post=$postId",
						"untrash-post_$postId"
					);
					echo '<a href="' . esc_url($restore_link) . '" class="wc-dashicons viewIcon" title="Restore"> <span class="dashicons dashicons-undo"></span> </a>';
				}
				break;

		}	
	}
	
	public function get_products_without_donation( $query ) {
		
		// Do nothing if not on product Admin page
		if ( ! is_admin() ) :
			return;
		endif;
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$flag = apply_filters( 'wc_donation_show_hidden_products', false );

		// Make sure we're talking to the WP_Query
		if ( $query->is_main_query() && isset($query->query[ 'post_type' ]) && 'product' === $query->query[ 'post_type' ] && ! $flag ) :

			// this will hide campaign created product from product list in admin
			$query->set( 'post__not_in', self::get_donation_ids() );

		endif;
	}

	public static function get_campaign_id_by_product_id ( $id ) {
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$campaigns = get_posts( apply_filters ('wc_donation_get_campaign_id_by_product_id', array (
			'fields'          => 'ids',
			'posts_per_page'  => -1,
			'post_type' => 'wc-donation'
		) ) );

		foreach ( $campaigns as $campaign ) {
			
			$prod_id = get_post_meta( $campaign, 'wc_donation_product', true );
			if ( $prod_id == $id ) {
				return $campaign;
				exit;
			}	
		}

		return 0;
	}

	public static function get_donation_ids() {

		$campaigns = get_posts( array(
			'fields'          => 'ids',
			'posts_per_page'  => -1,
			'post_type' => 'wc-donation'
		) );

		$prod_ids = array();

		foreach ( $campaigns as $campaign ) {
			$prod_ids[] = get_post_meta( $campaign, 'wc_donation_product', true );	
		}

		return $prod_ids;
	}

	/**
	 * Create product for each campaign creates.
	 */
	public function wc_donation_save_post ( $post_id, $post, $updated ) {		

		if ( 'wc-donation' == $post->post_type && 'publish' == $post->post_status ) {			

			$post_title = isset( $post->post_title ) ? $post->post_title : '';

			$prod_id = get_post_meta( $post_id, 'wc_donation_product', true );			

			if ( empty( $prod_id ) || empty( get_post_status ( $prod_id ) ) ) {
				$this->create_product_for_donation( $post_id, $post_title );
			} else {

				// delete attament image from product if exist.
				delete_post_meta( $prod_id, '_thumbnail_id' );

				//set campaign attachment_id to product attachment id
				$attachment_id = get_post_thumbnail_id( $post_id );
				if ( $attachment_id ) {
					set_post_thumbnail( $prod_id, $attachment_id );
				}				
				
				/**
				* Filter.
				* 
				* @since 3.4.5
				*/
				$product = apply_filters ( 'wc_donation_before_product_update', array (
					'ID' => $prod_id,
					'post_title' => $post_title,
					'post_name' => sanitize_title( 'WC Donation - ' . $post_title ),
					'post_status' => 'publish'
				), $post_id );
				wp_update_post( $product );
				$this->update_product_meta( $post_id, $prod_id );
			}
		}
	}

	/**
	 * Creating product dynamically
	 */
	private function create_product_for_donation ( $post_id, $post_title ) {
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$product_args = apply_filters ( 'wc_donation_before_product_create', array (
			'post_title' => $post_title,
			'post_type' => 'product',
			'post_status' => 'publish',
			'post_name' => sanitize_title( 'WC Donation - ' . $post_title )
		) );

		$prod_id = wp_insert_post( $product_args );

		if ( ! empty($prod_id) ) {			
			$this->update_product_meta( $post_id, $prod_id );
		}

	}

	private function update_product_meta ( $post_id, $prod_id ) {

		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		do_action('wc_donation_before_save_product_meta', $post_id, $prod_id );

		$RecurringDisp = get_post_meta ( $post_id, 'wc-donation-recurring', true  );
		$interval = get_post_meta ( $post_id, '_subscription_period_interval', true  );
		$period = get_post_meta ( $post_id, '_subscription_period', true  );
		$length = get_post_meta ( $post_id, '_subscription_length', true  );

		$singlePage = get_post_meta ( $post_id, 'wc-donation-disp-single-page', true );
		$shopVisible = get_post_meta ( $post_id, 'wc-donation-disp-shop-page', true );

		if ( 'yes' == $singlePage && 'yes' == $shopVisible ) {
			wp_set_object_terms( $prod_id, array( 'exclude-from-search' ), 'product_visibility' );
			update_post_meta( $prod_id, '_visibility', '_visibility_visible' );
		} else {
			wp_set_object_terms( $prod_id, array( 'exclude-from-catalog', 'exclude-from-search' ), 'product_visibility' );
			update_post_meta( $prod_id, '_visibility', '_visibility_hidden' );
		}

		if ( 'disabled' == $RecurringDisp || empty($RecurringDisp) ) {
			wp_set_object_terms( $prod_id, 'simple', 'product_type' );
		} else {
			wp_set_object_terms( $prod_id, 'subscription', 'product_type' );
			delete_post_meta( $prod_id, '_subscription_price' );
			delete_post_meta( $prod_id, '_subscription_period_interval' );
			delete_post_meta( $prod_id, '_subscription_period' );
			delete_post_meta( $prod_id, '_subscription_length' );

			update_post_meta( $prod_id, '_subscription_price', '0' );		
			update_post_meta( $prod_id, '_subscription_period_interval', $interval );		
			update_post_meta( $prod_id, '_subscription_period', $period );		
			update_post_meta( $prod_id, '_subscription_length', $length );	
			if ( 'user' == $RecurringDisp || empty($RecurringDisp ) ) {
				update_post_meta( $prod_id, '_subscription_period_interval', '1' );		
				update_post_meta( $prod_id, '_subscription_period', 'day' );		
				update_post_meta( $prod_id, '_subscription_length', '1' );
			}
		}
		update_post_meta( $prod_id, '_downloadable', 'yes' );
		update_post_meta( $prod_id, '_stock_status', 'instock');
		update_post_meta( $prod_id, 'total_sales', '0' );
		update_post_meta( $prod_id, '_tax_status', 'none' );
		update_post_meta( $prod_id, '_virtual', 'yes' );
		update_post_meta( $prod_id, '_regular_price', '0' );
		update_post_meta( $prod_id, '_sale_price', '' );
		update_post_meta( $prod_id, '_price', '0' );
		update_post_meta( $prod_id, '_purchase_note', '' );
		update_post_meta( $prod_id, '_featured', 'no' );
		update_post_meta( $prod_id, '_weight', '' );
		update_post_meta( $prod_id, '_length', '' );
		update_post_meta( $prod_id, '_width', '' );
		update_post_meta( $prod_id, '_height', '' );
		update_post_meta( $prod_id, '_sku', $prod_id );
		update_post_meta( $prod_id, '_product_attributes', array() );
		update_post_meta( $prod_id, '_sale_price_dates_from', '' );
		update_post_meta( $prod_id, '_sale_price_dates_to', '' );		
		update_post_meta( $prod_id, '_sold_individually', '' );
		update_post_meta( $prod_id, '_manage_stock', 'no' );
		update_post_meta( $prod_id, '_backorders', 'no' );
		update_post_meta( $prod_id, 'is_wc_donation', 'donation' );

		//set campaign attachment_id to product attachment id
		$attachment_id = get_post_thumbnail_id( $post_id );
		if ( $attachment_id ) {
			set_post_thumbnail( $prod_id, $attachment_id );
		}

		//adding product id into camapaign as meta value
		update_post_meta( $post_id, 'wc_donation_product', $prod_id  );

		//adding campaign id into product as meta value two way sync
		update_post_meta( $prod_id, 'wc_donation_campaign', $post_id  );
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		do_action('wc_donation_after_save_product_meta', $post_id, $prod_id );
	}

	/**
	 * Register a custom post type called "wc-donation".
	 *
	 * @see get_post_type_labels() for label keys.
	 */
	public function wc_donation_posttype () {
		$labels = array(
			'name'                  => _x( 'WC Donation', 'Post type general name', 'wc-donation' ),
			'singular_name'         => _x( 'WC Donation', 'Post type singular name', 'wc-donation' ),
			'menu_name'             => _x( 'WC Donation', 'Admin Menu text', 'wc-donation' ),
			'name_admin_bar'        => _x( 'WC Donation', 'Add New on Toolbar', 'wc-donation' ),
			'add_new'               => __( 'Add New', 'wc-donation' ),
			'add_new_item'          => __( 'Add New Campaign', 'wc-donation' ),
			'new_item'              => __( 'New Campaign', 'wc-donation' ),
			'edit_item'             => __( 'Edit Campaign', 'wc-donation' ),
			'view_item'             => __( 'View Campaign', 'wc-donation' ),
			'all_items'             => __( 'All Campaigns', 'wc-donation' ),
			'search_items'          => __( 'Search Campaign', 'wc-donation' ),
			'parent_item_colon'     => __( 'Parent Campaign:', 'wc-donation' ),
			'not_found'             => __( 'No Campaign found.', 'wc-donation' ),
			'not_found_in_trash'    => __( 'No Campaign found in Trash.', 'wc-donation' ),
			'featured_image'        => _x( 'Campaign Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'wc-donation' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'wc-donation' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'wc-donation' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'wc-donation' ),
			'archives'              => _x( 'Campaign archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'wc-donation' ),
			'insert_into_item'      => _x( 'Insert into Campaign', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'wc-donation' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this Campaign', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'wc-donation' ),
			'filter_items_list'     => _x( 'Filter Campaign list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'wc-donation' ),
			'items_list_navigation' => _x( 'Campaign list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'wc-donation' ),
			'items_list'            => _x( 'Campaign list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'wc-donation' ),
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			// 'rest_namespace'     => 'wc-donation/v1',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'wc-donation' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			//'menu_position'      => 56,
			'menu_icon'           => 'dashicons-heart',
			'supports'           => array( 'title', 'thumbnail' ),
		);
	
		register_post_type( 'wc-donation', $args );
	}

	/**
	 * Craete plugin menu page
	 */
	public function create_sub_menu () {

		add_submenu_page ( 
			__( 'edit.php?post_type=wc-donation', 'wc-donation' ), 
			__( 'Home', 'wc-donation' ),
			__( 'Home', 'wc-donation' ),
			__( 'manage_options', 'wc-donation' ),
			__( 'home', 'wc-donation' ),
			array ( $this, 'wc_donation_home_view' ),
			0
		);

		add_submenu_page ( 
			__( 'edit.php?post_type=wc-donation', 'wc-donation' ), 
			__( 'General Settings', 'wc-donation' ),
			__( 'General Settings', 'wc-donation' ),
			__( 'manage_options', 'wc-donation' ),
			__( 'general', 'wc-donation' ),
			array ( $this, 'wc_donation_setting_view' ),
			10
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Wc Donation Home View
	 */
	public function wc_donation_home_view () {
		include WC_DONATION_PATH . '/includes/views/admin/home.php';
	}

	/**
	 * General Setting View
	 */
	public function wc_donation_setting_view () {
		include WC_DONATION_PATH . '/includes/views/admin/general-setting.php';
	}

	/**
	 * Regiser settings
	 */
	public function register_settings() {		
		
		if ( isset( $_POST[ 'option_page' ] ) ) {
			if ( ! empty( $_POST[ 'option_page' ] && !wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'wc-donation-general-settings-group') ) ) {
				$option_page = sanitize_text_field( $_POST[ 'option_page' ] );


				
				switch ( $option_page ) {
					
					case 'wc-donation-general-settings-group':
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-checkout-product');
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-cart-product');
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-fees-product');
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-product');
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-on-checkout' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-on-cart' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-on-round' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-card-fee' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-recommended' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-multiplier' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-fees-type' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-fees-percent' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-field-label' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-field-message' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-fees-field-message' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-button-text' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-button-cancel-text' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-button-color' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-round-button-text-color' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-gift-aid' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-gift-aid-area' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-gift-aid-title' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-gift-aid-checkbox-title' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-gift-aid-explanation' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-gift-aid-declaration' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-pdf-receipt' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-tributes' );
						register_setting( 'wc-donation-general-settings-group', 'wc-donation-api' );
						break;
				}
			}
		}
	}
	
	/**
	 * Wc_cart_item_quantity
	 *
	 * @param  mixed $product_quantity
	 * @param  mixed $cart_item_key
	 * @param  mixed $cart_item
	 * @return void
	 */
	public function wc_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		if ( is_cart() ) {
			if ( isset( $cart_item['campaign_id'] ) ) {
				$product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] ); 
			}
		   
		}
		return $product_quantity;
	}

}

new WcdonationSetting();
