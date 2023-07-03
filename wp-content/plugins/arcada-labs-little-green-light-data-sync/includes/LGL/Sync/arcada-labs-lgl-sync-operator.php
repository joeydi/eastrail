<?php
/**
 * Sync operators.
 *
 * @link       https://arcadalabs.com
 * @since      1.0.0
 *
 * @package    Arcada_Labs_Little_Green_Light_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Sync/admin
 */

namespace ArcadaLabs\LGL\Sync;

use ArcadaLabs\Utils\GFUtils;
use ArcadaLabs\Constants\Names;
use WP_REST_Server;
use WP_REST_Response;
use ArcadaLabs\LGL\LGLCore;
use WC_Customer;
use GFAPI;

class Arcada_Labs_LGL_Sync_Operator
{
    private $lgl;

    public function __construct()
    {
        $this->lgl = new LGLCore();
        $this->access = $this->lgl->licenseActive();
    }

    /**
     * Creates the object for the webhook post
     * @param $constituent
     * @param $gift
     * @return array|null[]
     */
    public function makeWebHookData($constituent, $gift)
    {
        return array(
            'external_id'=>$gift['external_id'] ?? null,
            'gift_type_name'=>$gift['gift_type_name'] ?? null,
            'payment_type_name'=>$gift['payment_type_name'] ?? null,
            'received_amount'=>$gift['received_amount'] ?? null,
            'deductible_amount'=>$gift['deductible_amount'] ?? null,
            'deposited_amount'=>$gift['deposited_amount'] ?? null,
            'campaign_name'=>$gift['campaign_name'] ?? null,
            'fund_name'=>$gift['fund_name'] ?? null,
            'category_name'=>$gift['category_name'] ?? null,
            'external_constituent_id'=>$constituent['external_constituent_id'] ?? null,
            'first_name'=>$constituent['first_name'] ?? null,
            'last_name'=>$constituent['last_name'] ?? null,
            'street'=>$constituent['street'] ?? null,
            'city'=>$constituent['city'] ?? null,
            'postal_code'=>$constituent['postal_code'] ?? null,
            'country'=>$constituent['country'] ?? null,
            'state'=>$constituent['state'] ?? null,
            'email'=>$constituent['email'] ?? null,
        );
    }

    /**
     * Make gift object from a WC order, returns false if there are no products on the order selected to sync on LGL
     * @param $order
     * @return array|false
     */
    public function makeGiftFromWCOrder($order)
    {
        $gifts = [];

        foreach ($order->get_items() as $item_key => $item) {
            $item_id = $item->get_product_id();
            // verify if the product should be synced on LGL
            $sync_product = get_post_meta($item_id, '_lgl_sync', true);

            if ($sync_product) {
                $product_category = get_post_meta($item_id, '_lgl_category', true);
                $product_campaign = get_post_meta($item_id, '_lgl_campaign', true);
                $product_fund = get_post_meta($item_id, '_lgl_fund', true);
                $product_gift_type = get_post_meta($item_id, '_lgl_gift_type', true);
                if (empty($product_gift_type)) {
                    $product_gift_type = 'Gift';
                }

                if($gifts[$product_campaign] ?? false) {
                    // non discounted
                    $gifts[$product_campaign]['received_amount'] = $gifts[$product_campaign]['received_amount'] + $item->get_subtotal();
                    $gifts[$product_campaign]['deductible_amount'] = $gifts[$product_campaign]['deductible_amount'] + $item->get_subtotal();
                    // discounted
                    $gifts[$product_campaign]['deposited_amount'] = $gifts[$product_campaign]['deposited_amount'] + $item->get_total();
                } else {
                    $gifts[$product_campaign] = [
                        'external_id' => $order->get_id(),
                        'note_text' => $item->get_name(),
                        'gift_type_name' => $product_gift_type ?? 'Gift',
                        'payment_type_name' => get_option('arcada_labs_lgl_sync_wc_payment_type') ?? 'Credit Card',
                        'received_amount' => $item->get_subtotal(),
                        'deductible_amount' => $item->get_subtotal(),
                        'deposited_amount' => $item->get_total()
                    ];
                    if ($product_category) {
                        $gifts[$product_campaign]['category_name'] = $product_category;
                    }
                    if ($product_campaign) {
                        $gifts[$product_campaign]['campaign_name'] = $product_campaign;
                    }
                    if ($product_fund) {
                        $gifts[$product_campaign]['fund_name'] = $product_fund;
                    }
                }
            }
        }

        // if at least one detail was set to be synced to LGL we push the info
        return $gifts;
    }

    /**
     * Creates the gift part of the information for the webhook post
     * @param $entry
     * @param $fields
     * @return array
     */
    public function makeGiftFromGF($entry, $fields, $category, $campaign, $fund, $gift_type, $payment_type): array
    {
        $gift = [
            'external_id'=>$entry['id'],
            'received_date'=>$entry['date_created'],
            'gift_type_name' => $gift_type ?? 'Gift',
            'payment_type_name' => $payment_type ?? 'Online - Credit Card',
            'received_amount' => $entry[$fields['total']],
            'deductible_amount' => $entry[$fields['total']],
            'deposited_amount' => $entry[$fields['total']]
        ];

        if ($campaign) {
            $gift['campaign_name'] = $campaign;
        }

        if ($fund) {
            $gift['fund_name'] = $fund;
        }

        if ($category) {
            $gift['category_name'] = $category;
        }

        return $gift;
    }

    /**
     * Make the constituent part of the information for the webhook post from a WC order
     * @param $order
     * @return array
     */
    public function makeConstituentFromWCOrder($order)
    {
        return [
            'external_constituent_id'=>'anonymous'.$order->get_id(),
            'first_name'=>$order->get_billing_first_name(),
            'last_name'=>$order->get_billing_last_name(),
            'city'=>$order->get_billing_city(),
            'country'=>$order->get_billing_country(),
            'email'=>$order->get_billing_email(),
            'phone'=>$order->get_billing_phone(),
            'postal_code'=>$order->get_billing_postcode(),
            'state'=>$order->get_billing_state(),
            'address_line_1'=>$order->get_billing_address_1(),
            'address_line_2'=>$order->get_billing_address_2(),
        ];
    }

    /**
     * Creates a Constituent object to be created or updated on LGL
     * @param $user_id
     * @return array
     * @throws \Exception
     */
    public function makeConstituentFromWCUser($user_id): array
    {
        $data = new WC_Customer($user_id);
        if (!$data->get_first_name()) {
            $first_name = $data->get_username();
        } else {
            $first_name = $data->get_first_name();
        }

        $constituent = [
            'external_constituent_id'=>$user_id,
            'first_name'=>$first_name,
            'last_name'=>$data->get_last_name(),
            'email'=>$data->get_email(),
        ];

        if ($street  = $data->get_billing_address_1()) {
            $constituent['street'] = $street;

            if ($city = $data->get_billing_city()) {
                $constituent['city'] = $city;
            }
            if ($postal_code = $data->get_billing_postcode()) {
                $constituent['postal_code'] = $postal_code;
            }
            if ($country = $data->get_billing_country()) {
                $constituent['country'] = $country;
            }
            if ($state = $data->get_billing_state()) {
                $constituent['state'] = $state;
            }
        }

        return $constituent;
    }

    /**
     * Make the constituent part of the information for the webhook post from a Gravity Form entry
     * @param $entry
     * @param $fields
     * @return array
     */
    public function makeConstituentFromGF($entry, $fields)
    {
        $constituent = [
            'first_name'=>$entry[$fields['first_name']],
            'last_name'=>$entry[($fields['last_name']  ?? '')] ?? '',
            'email' => $entry[$fields['email']],
        ];

        if($fields['street'] ?? false) {
            if ($street = $entry[$fields['street']]) {
                $constituent['street'] = $street;

                if ($city = $entry[$fields['city']]) {
                    $constituent['city'] = $city;
                }

                if ($state = $entry[$fields['state']]) {
                    $constituent['state'] = $state;
                }

                if ($zip = $entry[$fields['zip']]) {
                    $constituent['postal_code'] = $zip;
                }

                if ($country = $entry[$fields['country']]) {
                    $constituent['country'] = $country;
                }

            }
        }

        return $constituent;
    }

    /**
     * Checks if the form has the required fields
     * @param $fields
     * @return bool
     */
    public function gFormFieldsValid($fields)
    {
        return (array_key_exists('first_name', $fields) && array_key_exists('email', $fields));
    }

	/**
	 * Gets data from lgl or cache
	 * @return mixed|string|void
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	private function getLglInfo($data_name, $force = false)
	{
		if (!$force) {
			if ( $last_check = get_option( Names::DATA_CHECKS[ $data_name ] ) ) {
				if ( $last_check === date( "Y-m-d" ) ) {
					return get_option( Names::DATA_SETS[ $data_name ] );
				} else {
					update_option( Names::DATA_CHECKS[ $data_name ], date( 'Y-m-d' ) );
				}
			} else {
				add_option( Names::DATA_CHECKS[ $data_name ], date( 'Y-m-d' ) );
				update_option( Names::DATA_CHECKS[ $data_name ], date( 'Y-m-d' ) );
			}
		}

		$this->lgl->get($data_name, array('limit'=>'200'));

		if (get_option(Names::DATA_SETS[$data_name])) {
			update_option(Names::DATA_SETS[$data_name], $this->lgl->getResponse());
		} else {
			add_option(Names::DATA_SETS[$data_name], $this->lgl->getResponse());
		}

		return get_option(Names::DATA_SETS[$data_name]);
	}

    /**
     * Gets the funds options list
     * @return mixed|string|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFunds()
    {
		return $this->getLglInfo('funds');
    }

    /**
     * Gets the Campaigns options list
     * @return mixed|string|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCampaigns()
    {
	    return $this->getLglInfo('campaigns');
    }

    /**
     * Gets the Gift Types options list
     * @return mixed|string|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getGiftTypes()
    {
	    return $this->getLglInfo('gift_types');
    }

    /**
     * Gets the Payment Types options list
     * @return mixed|string|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPaymentTypes()
    {
	    return $this->getLglInfo('payment_types');
    }

    /**
     * Gets the Categories options list
     * @return mixed|string|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCategories()
    {
	    return $this->getLglInfo('gift_categories');
    }

    /**
     * Run the Constituent sync to LGL, creating a constituent per each customer and subscriber users on the WP
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function run_constituent_sync()
    {
        $constituents = get_users(array('role__in'=>array('customer', 'subscriber')));

        header('Content-Type: text/plain');
        ob_start();

        foreach ($constituents as $user) {
            $constituent = $this->makeConstituentFromWCUser($user->ID);
            $data = new WC_Customer($user->ID);

            $hook_data = $this->makeWebHookData($constituent, []);
            $this->lgl->hookCall($hook_data);

            $chunk = json_encode(array($hook_data)) . "\n";

            // Send the current chunk
            echo $chunk . "\r\n";

            // Flush the output buffer
            flush();
            ob_flush();
        }

        ob_end_flush();
    }

    /**
     * Function to activate the license to use the paid sections of the plugin
     * @param $request
     * @return WP_REST_Response
     */
    public function activate_license_key($request) {
        $params = $request->get_params();
        $license = $params['license'];

        if (get_option(Names::LICENSES['license']) || (get_option(Names::LICENSES['license']) == '')) {
            $raw_response['updated'] = update_option(Names::LICENSES['license'], $license);
        } else {
            $raw_response['added'] = add_option(Names::LICENSES['license'], $license);
        }
        $raw_response['url'] = $this->lgl->license($license);
        $data = $this->lgl->getResponse();
        $raw_response['data'] = $data;

        if ($data->pair ?? false) {
            if (get_option(Names::LICENSES['pair'])) {
                update_option(Names::LICENSES['pair'], $data->pair);
                update_option(Names::ACCESS_LEVELS['GF_LICENSE'], $data->access->GF_LICENSE);
                update_option(Names::ACCESS_LEVELS['WC_LICENSE'], $data->access->WC_LICENSE);
            } else {
                add_option(Names::LICENSES['pair'], $data->pair);
                add_option(Names::ACCESS_LEVELS['GF_LICENSE'], $data->access->GF_LICENSE);
                add_option(Names::ACCESS_LEVELS['WC_LICENSE'], $data->access->WC_LICENSE);
            }
        } else {

			if ($data->message ?? false) {
				if ($data->message === 'Invalid license') {
					update_option(Names::ACCESS_LEVELS['GF_LICENSE'], false);
					update_option(Names::ACCESS_LEVELS['WC_LICENSE'], false);
					$raw_response['goner'] = 'all false';
				}
				$raw_response['message'] = $data->message;
			}

            $response = new WP_REST_Response($raw_response);
            $response->set_status(400);

            return $response;
        }

        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    public function deactivate_license_key($request) {
	    delete_option( Names::DATA_CHECKS[ 'license' ] );
        $this->lgl->removeLicense();
        $this->lgl->getResponse();
        $raw_response['status'] = 'ok';
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    /**
     * Run the Constituent and Gift sync to LGL from the selected forms, per form search the proper fields
     * if the structure is correct then each entry will try and sync as a constituent and gift
     * searching first if either already exists to prevent duplicates
     * @return WP_REST_Response | void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function run_forms_sync()
    {
        if ($this->access[\ArcadaLabs\Constants\Names::TIERS['GF_LICENSE']]) {
            header('Content-Type: text/plain');
            ob_start();
            $raw_response = [];
            if ($gv_forms = get_option('arcada_labs_lgl_sync_settings_field_forms')) {
                $selected_forms = $gv_forms['form'] ?? [];
                $selected_categories = $gv_forms['category'] ?? [];
                $selected_campaigns = $gv_forms['campaign'] ?? [];
                $selected_funds = $gv_forms['fund'] ?? [];
                $selected_gift_types = $gv_forms['gift_type'] ?? [];
                $selected_payment_types = $gv_forms['payment_type'] ?? [];
                foreach ($selected_forms as $index => $form_id) {

                    $fields = GFUtils::getFieldsFromForm($form_id);

                    // if it has a first_name, email and a total then it can be synced
                    if ($this->gFormFieldsValid($fields)) {
                        $page = 1;
                        $page_size = 100;

                        do {

                            $entries = GFAPI::get_entries(
                                $form_id,
                                array(
                                    'status' => 'active',
                                    'offset' => ($page - 1) * $page_size,
                                    'page_size' => $page_size
                                )
                            );

                            foreach ($entries as $entry) {
                                $constituent = $this->makeConstituentFromGF($entry, $fields);

                                if (array_key_exists('total', $fields)) {

                                    $gift_data = $this->makeGiftFromGF(
                                        $entry,
                                        $fields,
                                        $selected_categories[$index] ?? false,
                                        $selected_campaigns[$index] ?? false,
                                        $selected_funds[$index] ?? false,
                                        $selected_gift_types[$index] ?? false,
                                        $selected_payment_types[$index] ?? false,
                                    );
                                    $hook_data = $this->makeWebHookData($constituent, $gift_data);
                                    $hook_data['received_date'] = $entry['date_created'];
                                } else {
                                    $hook_data = $this->makeWebHookData($constituent, []);
                                }


                                $raw_response[$entry['id']] = $hook_data;

                                $this->lgl->hookCall($hook_data);

                                $chunk = json_encode(array($hook_data)) . "\n";
                                // Send the current chunk
                                echo $chunk . "\r\n";

                                // Flush the output buffer
                                flush();
                                ob_flush();

                            }
                        } while(count($entries) >= $page_size);
                    }
                }
            }
            ob_end_flush();
        } else {
            $response = new WP_REST_Response('Invalid License');
            $response->set_status(403);
            return $response;
        }
    }

    /**
     * Transactions sync of all orders that don't have an LGL id but do have a user
     * Constituents sync should have been run previously
     * @return WP_REST_Response | void
     */
    public function run_transaction_sync()
    {
        if ($this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']]) {
            header('Content-Type: text/plain');
            ob_start();

            $raw_response = [];
            $page = 1;
            $per_page = 100;


            do {
                $args = array(
                    'status'         => array('wc-completed'),
                    'limit'          => $per_page,
                    'page'           => $page,
                    'paginate'       => true,
                );

                $page++;
                $orders = wc_get_orders($args);

                foreach ($orders->orders as $order) {
                    if (is_a($order, 'WC_Order')) {

                        if ($user_id = $order->get_user_id()) {
                            $constituent_data = $this->makeConstituentFromWCUser($user_id);
                        } else {
                            $constituent_data = $this->makeConstituentFromWCOrder($order);
                        }
                        $order_id = $order->get_id();


                        if ($gifts = $this->makeGiftFromWCOrder($order)) {

                            foreach ($gifts as $gift) {
                                $hook_data = $this->makeWebHookData($constituent_data, $gift);
                                $hook_data['received_date'] = $order->get_date_completed()->date_i18n();
                                $raw_response[$order_id] = $hook_data;

                                $this->lgl->hookCall($hook_data);

                                $chunk = json_encode(array($hook_data)) . "\n";

                                // Send the current chunk
                                echo $chunk . "\r\n";

                                // Flush the output buffer
                                flush();
                                ob_flush();

                            }
                        }
                    }
                }

            } while (is_countable($orders->orders) && count($orders->orders) >= $per_page);

            ob_end_flush();
        } else {
            $response = new WP_REST_Response('Invalid license');
            $response->set_status(403);
            return $response;
        }

    }

    /**
     * Call the function to make the webhook post
     * @param $data
     */
    public function hookCall($data)
    {
        $this->lgl->hookCall($data);
    }

    /**
     * Gets the access the license
     * @return array|false[]
     */
    public function get_license_tiers()
    {
        return $this->access;
    }
}
