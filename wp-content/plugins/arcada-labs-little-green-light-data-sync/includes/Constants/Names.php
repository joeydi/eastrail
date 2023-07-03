<?php
namespace ArcadaLabs\Constants;

abstract class Names
{
    const OPTIONS = array(
        "last_check"=>"arcada_labs_lgl_wizard_last_check",
        "start_step"=>"arcada_labs_lgl_wizard_start_step",
        "license_step"=>"arcada_labs_lgl_wizard_license_step",
        "api_key"=>"arcada_labs_lgl_wizard_api_key",
        "webhook_url"=>"arcada_labs_lgl_wizard_webhook_url",
        "forms_filled"=>"arcada_labs_lgl_wizard_forms_filled",
        "wc_products"=>"arcada_labs_lgl_wizard_wc_products",
        "initial_sync"=>"arcada_labs_lgl_wizard_initial_sync",
        "complete"=>"arcada_labs_lgl_wizard_complete",
    );

	const DATA_SETS = array(
		"funds"=>"arcada_labs_lgl_funds",
		"campaigns"=>"arcada_labs_lgl_campaigns",
		"gift_types"=>"arcada_labs_lgl_gift_types",
		"payment_types"=>"arcada_labs_lgl_payment_types",
		"gift_categories"=>"arcada_labs_lgl_gift_categories",
		"license"=>"arcada_labs_lgl_license",
	);

	const DATA_CHECKS = array(
		"funds"=>"arcada_labs_lgl_funds_check",
		"campaigns"=>"arcada_labs_lgl_campaigns_check",
		"gift_types"=>"arcada_labs_lgl_gift_types_check",
		"payment_types"=>"arcada_labs_lgl_payment_types_check",
		"gift_categories"=>"arcada_labs_lgl_gift_categories_check",
		"license"=>"arcada_labs_lgl_license_check",
	);

    const LICENSES = array(
        "license" => "arcada_labs_lgl_sync_license_key",
        "pair" => "arcada_labs_lgl_license_pair",
    );

    const ACCESS_LEVELS = array(
        'GF_LICENSE'=>'arcada_labs_lgl_sync_gf_level',
        'WC_LICENSE'=>'arcada_labs_lgl_sync_wc_level'
    );

    const TIERS = array(
        "GF_LICENSE"=>"GF_LICENSE",
        "WC_LICENSE"=>"WC_LICENSE",
    );
}
