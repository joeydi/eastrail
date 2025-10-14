<?php

	/**
	* Action woocommerce_after_add_to_cart_form.
	* 
	* @since 3.9.5
	*/
	do_action('woocommerce_after_add_to_cart_form');
if (WC()->checkout()->get_checkout_fields()) {
	/**
	* Action woocommerce_checkout_before_customer_details.
	* 
	* @since 3.9.5
	*/
	do_action('woocommerce_checkout_before_customer_details');
	
	/**
	* Action woocommerce_checkout_billing.
	* 
	* @since 3.9.5
	*/
	do_action('woocommerce_checkout_billing');
	
	/**
	* Action woocommerce_checkout_shipping.
	* 
	* @since 3.9.5
	*/
	do_action('woocommerce_checkout_shipping');
	
	/**
	* Action woocommerce_checkout_after_customer_details.
	* 
	* @since 3.9.5
	*/
	do_action('woocommerce_checkout_after_customer_details');
}
