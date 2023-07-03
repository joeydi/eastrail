<?php
/**
 * Wizard operators.
 *
 * @link       https://arcadalabs.com
 * @since      1.0.0
 *
 * @package    Arcada_Labs_Little_Green_Light_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Sync/admin
 */

namespace ArcadaLabs\Wizard;

use ArcadaLabs\Constants\Names;
use Faker\Guesser\Name;
use WP_REST_Response;
use ArcadaLabs\LGL\Sync\Arcada_Labs_LGL_Sync_Operator;


class Arcada_Labs_Wizard_Operator
{
    private $sync_operator;

    public function __construct()
    {
        $this->sync_operator = new Arcada_Labs_LGL_Sync_Operator();
    }

    private function start_step($request)
    {
        $params = $request->get_params();

        if (get_option(Names::OPTIONS['start_step'])) {
            update_option(Names::OPTIONS['start_step'], '1');
        } else {
            add_option(Names::OPTIONS['start_step'], '1');
        }

        $key = $params['dashboardUrl'];
        $dashboard_url = explode('.com', $key)[0] . '.com';

        if (get_option('arcada_labs_lgl_sync_dashboard_link')) {
            update_option('arcada_labs_lgl_sync_dashboard_link', $dashboard_url);
        } else {
            add_option('arcada_labs_lgl_sync_dashboard_link', $dashboard_url);
            update_option('arcada_labs_lgl_sync_dashboard_link', $dashboard_url);
        }

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    private function license_step($request)
    {
        $params = $request->get_params();

        if (get_option(Names::OPTIONS['license_step'])) {
            update_option(Names::OPTIONS['license_step'], '1');
        } else {
            add_option(Names::OPTIONS['license_step'], '1');
        }

        if (!empty($params['license'])) {
            return $this->sync_operator->activate_license_key($request);
        } else {
            update_option(Names::LICENSES['license'], '');
        }

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    private function api_key_step($request)
    {
        $params = $request->get_params();
        $key = $params['key'];
		delete_option(Names::DATA_SETS['funds']);
		delete_option(Names::DATA_SETS['campaigns']);
		delete_option(Names::DATA_SETS['gift_types']);
		delete_option(Names::DATA_SETS['payment_types']);
		delete_option(Names::DATA_SETS['gift_categories']);

		delete_option(Names::DATA_CHECKS['funds']);
		delete_option(Names::DATA_CHECKS['campaigns']);
		delete_option(Names::DATA_CHECKS['gift_types']);
		delete_option(Names::DATA_CHECKS['payment_types']);
		delete_option(Names::DATA_CHECKS['gift_categories']);

        if (get_option(Names::OPTIONS['api_key'])) {
            update_option(Names::OPTIONS['api_key'], '1');
        } else {
            add_option(Names::OPTIONS['api_key'], '1');
        }

        if (get_option('arcada_labs_lgl_sync_settings_field_lgl_api_key')) {
            update_option('arcada_labs_lgl_sync_settings_field_lgl_api_key', $key);
        } else {
            add_option('arcada_labs_lgl_sync_settings_field_lgl_api_key', $key);
            update_option('arcada_labs_lgl_sync_settings_field_lgl_api_key', $key);
        }

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    private function webhook_step($request)
    {
        $params = $request->get_params();
        $key = $params['key'];

        if (get_option(Names::OPTIONS['webhook_url'])) {
            update_option(Names::OPTIONS['webhook_url'], '1');
        } else {
            add_option(Names::OPTIONS['webhook_url'], '1');
        }

        if (get_option('arcada_labs_lgl_webhook_url')) {
            update_option('arcada_labs_lgl_webhook_url', $key);
        } else {
            add_option('arcada_labs_lgl_webhook_url', $key);
            update_option('arcada_labs_lgl_webhook_url', $key);
        }

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    private function forms_step($request)
    {
        $params = $request->get_params();

        if (get_option(Names::OPTIONS['forms_filled'])) {
            update_option(Names::OPTIONS['forms_filled'], '1');
        } else {
            add_option(Names::OPTIONS['forms_filled'], '1');
            update_option(Names::OPTIONS['forms_filled'], '1');
        }

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    private function wc_products_step($request)
    {
        $params = $request->get_params();
	    $key = $params['payment_type'] ?? 'Credit Card';

        if (get_option(Names::OPTIONS['wc_products'])) {
            update_option(Names::OPTIONS['wc_products'], '1');
        } else {
            add_option(Names::OPTIONS['wc_products'], '1');
            update_option(Names::OPTIONS['wc_products'], '1');
        }

	    if (get_option('arcada_labs_lgl_sync_wc_payment_type')) {
		    update_option('arcada_labs_lgl_sync_wc_payment_type', $key);
	    } else {
		    add_option('arcada_labs_lgl_sync_wc_payment_type', $key);
		    update_option('arcada_labs_lgl_sync_wc_payment_type', $key);
	    }

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    private function initial_sync_step($request)
    {
        $params = $request->get_params();
        $skip = $params['skip'] ?? false;

        if (get_option(Names::OPTIONS['complete'])) {
            update_option(Names::OPTIONS['complete'], '1');
        } else {
            add_option(Names::OPTIONS['complete'], '1');
            update_option(Names::OPTIONS['complete'], '1');
        }

        if ($skip) {

            $raw_response['params'] = $params;
            $response = new WP_REST_Response($raw_response);
            $response->set_status(200);

            return $response;
        }

        $this->sync_operator->run_constituent_sync();
        $this->sync_operator->run_transaction_sync();
        $this->sync_operator->run_forms_sync();

        $raw_response['params'] = $params;
        $response = new WP_REST_Response($raw_response);
        $response->set_status(200);

        return $response;
    }

    public function wizard_flow($request)
    {
        $params = $request->get_params();
        $step = $params['step'];

        switch ($step) {
            case 'reset':
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['start_step']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['license_step']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['api_key']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['webhook_url']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['forms_filled']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['wc_products']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['initial_sync']);
                delete_option(\ArcadaLabs\Constants\Names::OPTIONS['complete']);
                $raw_response['params'] = $params;
                $response = new WP_REST_Response($raw_response);
                $response->set_status(200);
                return $response;
            case 'start':
                return $this->start_step($request);
            case 'licenseButton':
                return $this->license_step($request);
            case 'apiKeyButton':
                return $this->api_key_step($request);
            case 'webhookButton':
                return $this->webhook_step($request);
            case 'formsButton':
                return $this->forms_step($request);
            case 'wcProductsButton':
                return $this->wc_products_step($request);
            case 'initialSyncButton':
                return $this->initial_sync_step($request);
            default:
                $response = new WP_REST_Response("Invalid request");
                $response->set_status(400);

                return $response;
        }
    }
}
