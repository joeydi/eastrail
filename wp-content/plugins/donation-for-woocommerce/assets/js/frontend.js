$ = jQuery;

let flag = true;

if ( jQuery("form.woocommerce-checkout").length > 0 && !( jQuery("form.woocommerce-checkout").hasClass('wc-roundoff')  )  ) {
	jQuery("form.woocommerce-checkout").addClass('wc-roundoff');
}
dispaly_socialshare_links( jQuery );
jQuery(window).on('load', function(){
	if ( wcOrderScript.donationToOrder.is_checkout && wcOrderScript.donationToOrder.is_roundOff == 'yes' ) {
		jQuery( '.wc-block-components-checkout-place-order-button' ).on('click', function (e) {
			if ( flag ) {
				e.stopPropagation();
				add_popup_before_order();
			}
		});
	}
});

jQuery(window).on('load', function(){


	jQuery('.flip-clock').each(function() {
		var clockElement = jQuery(this);
		var clock;
		var startTimeStr = jQuery(this).data('start-time');
		var endTimeStr = jQuery(this).data('end-time');
		var endTimeArr = endTimeStr.split(':'); // Split the time string into hours and minutes
		var startTimeArr = startTimeStr.split(':'); // Split the start time string into hours and minutes
		var now = new Date();

		// Set the start and end times to today with the provided hours and minutes
		var startTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), parseInt(startTimeArr[0]), parseInt(startTimeArr[1]), 0);
		var endTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), parseInt(endTimeArr[0]), parseInt(endTimeArr[1]), 0);

		// If the end time is before the current time, set it to tomorrow with the same hours and minutes
		if (endTime.getTime() < now.getTime()) {
			endTime.setDate(endTime.getDate() + 1);
		}

		var diff = endTime.getTime() / 1000 - now.getTime() / 1000;
		diff = Math.max(diff, 0);
		clockElement.addClass('non-clickable');
		clock = jQuery(this).FlipClock(diff, {
			clockFace: 'HourlyCounter',
			countdown: true
		});
		console.log('startTime');
		console.log(startTime);
		console.log('endTime');
		console.log(endTime);

		// Check the remaining time and start time every second
		var interval = setInterval(function() {
			now = new Date();
			var remainingTime = clock.getTime().time;

			if (remainingTime <= 0) {
				clearInterval(interval);
				console.log('Reloading page');
				location.reload(); // Reload the page
			}
		}, 1000);

		// To forcefully set the remaining time to the clock instance
		clock.setTime(diff);
		clock.start();
	});


	// Initialize Pomodoro clock instances
	jQuery('.pomodoro-clock-container').each(function () {
		var container = this;
		var timerLabel = jQuery(container).find('.timer-label');
		var startTimeStr = jQuery(this).data('start-time');
		var endTimeStr = jQuery(this).data('end-time');

		var startTimeParts = startTimeStr.split(':');
		var endTimeParts = endTimeStr.split(':');

		var now = new Date();
		var start = new Date(now.getFullYear(), now.getMonth(), now.getDate(), startTimeParts[0], startTimeParts[1], 0);
		var end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), endTimeParts[0], endTimeParts[1], 0);

		if (end < start) {
			end.setDate(end.getDate() + 1);
		}

		var duration = (end - start) / 1000;
		var elapsedTime = (now - start) / 1000;
		var remainingTime = duration - elapsedTime;

		var storedRemainingTime = localStorage.getItem('remainingTime');
		var lastSaved = localStorage.getItem('lastSaved');
		if (storedRemainingTime && lastSaved) {
			var elapsedSinceLastSave = (now.getTime() - new Date(lastSaved).getTime()) / 1000;
			remainingTime = Math.max(0, storedRemainingTime - elapsedSinceLastSave);
		}

		var totalSecondsInDay = 24 * 60 * 60;
		remainingTime = remainingTime % totalSecondsInDay;

		var bar = new ProgressBar.Circle(container, {
			color: '#EFBBBE',
			trailColor: '#BF5252',
			trailWidth: 5,
			duration: remainingTime * 1000,
			easing: 'linear',
			strokeWidth: 5,
			from: { color: '#EFBBBE', width: 5 },
			to: { color: '#EFBBBE', width: 5 },
			step: function (state, circle) {
				var elapsed = (1.0 - circle.value()) * duration;
				var remaining = Math.max(duration - elapsed, 0);
				timerLabel.text(formatDuration(remaining));
				circle.path.setAttribute('stroke', state.color);
				circle.path.setAttribute('stroke-width', state.width);
			}
		});

		function updateProgressBar() {
			bar.set(remainingTime / duration);
			if (remainingTime <= 0) {
				clearInterval(progressInterval);
			}
		}

		var progressInterval = setInterval(updateProgressBar, 1100);

		var interval = setInterval(function () {
			now = new Date();
			elapsedTime = (now - start) / 1000;
			remainingTime = Math.max(duration - elapsedTime, 0);
			remainingTime = remainingTime % totalSecondsInDay;
			timerLabel.text(formatDuration(remainingTime));

			localStorage.setItem('remainingTime', remainingTime);
			localStorage.setItem('lastSaved', new Date().toISOString());

			if (remainingTime <= 1) {
				clearInterval(interval);
				location.reload();
			}
		}, 1000);

		jQuery(window).on('beforeunload', function () {
			now = new Date();
			elapsedTime = (now - start) / 1000;
			remainingTime = Math.max(duration - elapsedTime, 0);
			remainingTime = remainingTime % totalSecondsInDay;
			localStorage.setItem('remainingTime', remainingTime);
			localStorage.setItem('lastSaved', new Date().toISOString());
		});
	});

	jQuery('<style>')
		.prop('type', 'text/css')
		.html('\
        .flip-clock-wrapper .flip-clock-days {\
            display: none;\
        }\
    ')
		.appendTo('head');
});

function formatTime(date) {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	return (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes;
}

function formatDuration(seconds) {
	var hours = Math.floor(seconds / 3600);
	seconds %= 3600;
	var minutes = Math.floor(seconds / 60);
	var sec = Math.floor(seconds % 60);
	return (hours < 10 ? '0' : '') + hours + ':' +
		(minutes < 10 ? '0' : '') + minutes + ':' +
		(sec < 10 ? '0' : '') + sec;
}
//WC Donation Tabs Frontend
jQuery(document).on('click', '.wc-donation-tabs-wrap ul.tab-nav > li', function(event) {
	event.preventDefault();
	jQuery(this).siblings().removeClass('active');
	jQuery(this).addClass('active');
	var content = jQuery(this).data('tab');
	jQuery('.wc-donation-tab-content').removeClass('active');
	jQuery(`.wc-donation-tab-content.${content}`).addClass('active');
});

function add_popup_before_order () {
	jQuery.ajax({
		url: wcOrderScript.donationToOrder.ajaxUrl,
		type: "POST",
		dataType: "json",
		data: {
			action: 'add_popup_before_order',
			nonce: wcOrderScript.donationToOrder.nonce
		},
		beforeSend: function () {
			//if loader needs to add put here
		},
		success: function (response) {
			if (response['status']=='success') {
				jQuery(".wc-donation-popup").find("#wc-donation-price-" + response['campaign_id']).val(response['donation']);
				var text = jQuery(".wc-donation-popup").find(".donation_text").text();
				var res = text.replace("%amount%", response['donation']);
				jQuery(".wc-donation-popup").find(".donation_text").text(res);
				jQuery(".wc-donation-popup:not(.cart-campaign-popup)").addClass('wc-popup-show');
			} else {
				flag = false;
				jQuery( '.wc-block-components-checkout-place-order-button' ).trigger('click');
				jQuery("form.woocommerce-checkout").submit();
			}
		}
	});
}

if ( wcOrderScript.donationToOrder.is_checkout && ! wcOrderScript.donationToOrder.is_order_pay ) {
	/* when click on place order*/
	jQuery( document ).on(
		"click",
		"#place_order",
		function (e) {
			//console.log( wcOrderScript.donationToOrder.is_checkout );
			if ( wcOrderScript.donationToOrder.is_checkout && wcOrderScript.donationToOrder.is_roundOff == 'yes' ) {
				e.preventDefault();
				//debugger;
				add_popup_before_order();
			}
		}
	);
}

function NumbersOnly(myfield, e, dec) {
	/*if ( isNaN(removeCommas(myfield.value)) && myfield.value != "-") {
        return false;
	}*/
	// console.log(min);
	// console.log(max);

	// var priceEl = document.getElementById('wc-donation-price-' + id);
	var allowNegativeNumber = false;
	var key;
	var keychar;

	if (window.event)
		key = window.event.keyCode;
	else if (e)
		key = e.which;
	else
		return true;
	keychar = String.fromCharCode(key);
	var srcEl = e.srcElement ? e.srcElement : e.target;

	// if (typeof(priceEl) != 'undefined' && priceEl != null) {

	// 	priceEl.value = isFinite(parseFloat(myfield.value)) ? parseFloat(myfield.value) : '';
	// }
	// control keys
	if ((key == null) || (key == 0) || (key == 9) || (key == 13) || (key == 27) ) {
		return true;
	}

	// if ( min != '' && max != '' ) {
	// 	if ( ( parseFloat(myfield.value) >= min) && ( parseFloat(myfield.value) <= max) ) {
	// 		if (typeof(priceEl) != 'undefined' && priceEl != null) {
	// 			priceEl.value = isFinite(parseFloat(myfield.value)) ? parseFloat(myfield.value) : '';
	// 		}
	// 		return true;
	// 	} else {
	// 		myfield.value = '';
	// 		if (typeof(priceEl) != 'undefined' && priceEl != null) {
	// 			priceEl.value = isFinite(parseFloat(myfield.value)) ? parseFloat(myfield.value) : '';
	// 		}
	// 		return false;
	// 	}
	// }

	// numbers
	if ((("0123456789").indexOf(keychar) > -1) )
		return true;

	// decimal point jump
	else if (dec && (keychar == ".")) {
		//myfield.form.elements[dec].focus();
		return srcEl.value.indexOf(".") == -1;
	}

	else if (dec && (keychar == ",")) {
		return srcEl.value.indexOf(",") == -1;
	}

	//allow negative numbers
	else if (allowNegativeNumber && (keychar == "-")) {
		return (srcEl.value.length == 0 || srcEl.value == "0.00")
	}
	else
		return false;
}

jQuery(document).on('focusout', '.grab-donation', function() {
	var This = jQuery(this);
	var min = This.data('min');
	var max = This.data('max');
	var id = This.data('campaign_id');
	var rand_id = This.data('rand_id');
	var priceEl = document.getElementById('wc-donation-price-' + id + '_' + rand_id);
	var val = This.val();

	if ( max <= 0 && max != 'any' ) {
		max = val;
	}

	if ( (min == 'any' && max == 'any') || (val >= min && val <= max) ) {
		var procFees = jQuery('#processing-fees-' + id + '_' + rand_id).val();
		if ( wcOrderScript.donationToOrder.fees_type == 'percentage' ) {
			var donation_card_fee = val*(procFees/100);
		} else {
			var donation_card_fee = procFees;
		}

		var donation_summary_total = parseFloat(val) + parseFloat(donation_card_fee);

		if ( donation_summary_total > 0 && donation_summary_total != '' ) {
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-currency-symbol').attr('style', 'display: inline-block !important');
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-charge .wc-donation-amt').text(parseFloat(val).toFixed(2));
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-fee-summary .wc-donation-amt').text(parseFloat(donation_card_fee).toFixed(2));
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-summary-total .wc-donation-amt').text(parseFloat(donation_summary_total).toFixed(2));
		} else {
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-currency-symbol').attr('style', 'display: inline-block !important');
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-charge .wc-donation-amt').text(parseFloat(0).toFixed(2));
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-fee-summary .wc-donation-amt').text(parseFloat(0).toFixed(2));
			jQuery('#wc-donation-summary-' + id + '_' + rand_id + ' .wc-donation-summary-total .wc-donation-amt').text(parseFloat(0).toFixed(2));
		}
		priceEl.value = val;
	} else {
		priceEl.value = '';
		This.val('');
	}
});

jQuery(document).on("click", ".wc-close", function(){
	jQuery(this).parents(".wc-donation-popup").removeClass("wc-popup-show");
	if ( jQuery('body').hasClass('stopScroll') ) {
		jQuery('body').removeClass('stopScroll');
	}
});


function addDonationToOrder(type, amount, min_amount, max_amount, campaign_id, is_recurring, new_period, new_length, new_interval, tracker, donation_type, cause, fees, gift_aid='', tribute='', tribute_message='', cc_processing_fee, _this ,wps_sfw_sub_number, wps_sfw_sub_interval,wps_sfw_sub_exp_number,wps_sfw_sub_exp_interval, selectedLabel) {

	let skip = false;

	if ( type === 'roundoff-skip' ) {
		flag = false;
		jQuery( '.wc-block-components-checkout-place-order-button' ).trigger('click');
		jQuery("form.woocommerce-checkout").submit();
		return true;
	}

	if ( (amount != 0 && amount != null && amount > 0) ) {

		jQuery.ajax(
			{
				url: wcOrderScript.donationToOrder.ajaxUrl,
				type: "POST",
				dataType: "json",
				data: {
					action: wcOrderScript.donationToOrder.action,
					nonce: wcOrderScript.donationToOrder.nonce,
					campaign_id: campaign_id,
					amount: amount,
					selectedLabel: selectedLabel,
					cause: cause,
					fees: fees,
					type: type,
					tribute: tribute,
					tribute_message: tribute_message,
					gift_aid: gift_aid,
					is_recurring: is_recurring,
					new_period: new_period,
					new_length: new_length,
					new_interval: new_interval,
					cc_processing_fee: cc_processing_fee,
					wps_sfw_subscription_number : wps_sfw_sub_number,
					wps_sfw_subscription_interval : wps_sfw_sub_interval,
					wps_sfw_subscription_expiry_number : wps_sfw_sub_exp_number,
					wps_sfw_subscription_expiry_interval : wps_sfw_sub_exp_interval,
				},
				beforeSend: function() {
					jQuery(_this).parents('.wc_donation_on_cart').append('<div class="wc-donation-loader-css"><div class="wc-donation-blur-effect"></div><div class="wc-donation-loader" style="display:block;"></div></div>');
					jQuery(_this).parents('.widget_wc-donation-widget').append('<div class="wc-donation-loader-css"><div class="wc-donation-blur-effect"></div><div class="wc-donation-loader" style="display:block;"></div></div>');
				},
				success: function (response) {
					if (response['response'] == 'success') {
						jQuery(`input[value="${campaign_id}"]`).closest('.wc_donation_on_cart').find('div.wc-donation-loader-css').remove();
						jQuery(`input[value="${campaign_id}"]`).closest('.widget_wc-donation-widget').find('div.wc-donation-loader-css').remove();
						jQuery( "[name='update_cart']" ).prop( "disabled", false );
						jQuery( "[name='update_cart']" ).trigger( "click" );

						if (type==='roundoff') {
							jQuery(".wc-donation-popup").removeClass("wc-popup-show");
						}

						jQuery('body').trigger('update_checkout');
						if ( typeof wc !== 'undefined') {
							flag = false;
							const { extensionCartUpdate } = wc.blocksCheckout;
							extensionCartUpdate({
								namespace: 'dfw',
								data: {},
							});
						}

						if( jQuery("#wc-donation-woocommerce-notices-wrapper").length ) {
							jQuery("#wc-donation-woocommerce-notices-wrapper").show();
							document
								.getElementById("wc-donation-woocommerce-notices-wrapper")
								.scrollIntoView({ behavior: "smooth" });
						}
						if( response['cart_url'] != '' ) {
							window.location.href = response['cart_url'];
						}

						if ( response['checkoutUrl'] != '' ) {
							window.location.href = response['checkoutUrl'];
						}

						jQuery('body').trigger('wc_donation_checkout_event');

					}
					if ( 'failed' === response['response'] ) {
						jQuery(`input[value="${campaign_id}"]`).closest('.wc_donation_on_cart').find('div.wc-donation-loader-css').remove();
						jQuery(`input[value="${campaign_id}"]`).closest('.widget_wc-donation-widget').find('div.wc-donation-loader-css').remove();

						if( ! jQuery(`input[value="${campaign_id}"]`).siblings('div').hasClass('cc-require-message') ) {
							var messageElement = jQuery('<div class="cc-require-message">'+response.message+'</div>');
							jQuery(`input[value="${campaign_id}"]`).siblings('button').after(messageElement);
						}
					}
					console.log(response['response']);
					if ('error' === response['response']) {
						alert('Time Limit Exceeded');
						location.reload(); // This will reload the page when "OK" is clicked
					}
				}
			}
		);
		setTimeout(function() {
			jQuery('.cc-require-message').remove();
		}, 7000);
	} else {
		var text = wcOrderScript.donationToOrder.other_amount_placeholder;
		text = text.replace("%min%", min_amount);
		text = text.replace("%max%", max_amount);
		if ( donation_type == 'both' || donation_type == 'predefined' ) {
			if ( tracker == 1 ) {
				alert(`${text}`);
			} else {
				alert(`Please select amount`);
			}
		} else {
			alert(`${text}`);
		}
		return true;
	}

}

jQuery( document ).on("click", ".wc-donation-f-submit-donation, #wc-donation-round-f-submit-donation, #wc-donation-round-f-submit-skip-donation", function (e) {
		e.preventDefault();
		var _this = jQuery(this);
		var parents = jQuery(this).parents('.wc-donation-in-action');
		var type = jQuery(this).data('type');
		var campaign_id = jQuery(this).siblings('.wc_donation_camp').val();
		var rand_id = jQuery(this).siblings('.wp_rand').val();
		var is_recurring = false;
		var new_period = 'day';
		var new_length = '1';
		var new_interval = '1';
		var min_amount = 0;
		var max_amount = 0;
		var donation_type = jQuery(parents).data('donation-type');
		var tracker = jQuery(parents).find('.wc-opened').length;
		var subscription_data = [];
		var tracker2 = jQuery(parents).find('.wc-donation-f-donation-other-value').length;
		var selectBox = jQuery(parents).find('select.wc-label-select');
		selectedLabel = '';
		if (selectBox.length && selectBox.find('option:selected').val()) {
			selectedLabel = selectBox.find('option:selected').text().trim();
		}

		// If no valid selection from <select>, try radio buttons
		if (!selectedLabel) {
			var selectedRadio = jQuery(parents).find('input[name="wc_label_price"]:checked');
			if (selectedRadio.length) {
				selectedLabel = selectedRadio.closest('label').text().trim();
			}
		}
		if ( jQuery(this).data('min-value') ) {
			min_amount = jQuery(this).data('min-value');
		}

		if ( jQuery(this).data('max-value') ) {
			max_amount = jQuery(this).data('max-value');
		}

		if ( jQuery(parents).find('.donation-is-recurring').length > 0 && jQuery(parents).find('.donation-is-recurring').is(':checked') ) {
			is_recurring = jQuery(parents).find('.donation-is-recurring').val();
			new_period = jQuery(parents).find('._subscription_period').val();
			new_length = jQuery(parents).find('#_subscription_length').val();
			new_interval = jQuery(parents).find('#_subscription_period_interval').val();
		} else {
			is_recurring = 'no';
		}

		//alert(campaign_id);
		if ( type == 'roundoff' || type ==  'roundoff-skip' ) {
			jQuery(".wc-donation-popup").removeClass("wc-popup-show");
			var amount = jQuery('.roundoff-donation-price').val();
		} else {
			var id = '.donate_' + campaign_id + '_' + rand_id;
			//alert(id);
			var amount = jQuery(id).val();
		}
		var causeID = '.donate_cause_' + campaign_id + '_' + rand_id;
		var cause = jQuery(causeID).val();
		var feeCheck = '.donate_fees_' + campaign_id + '_' + rand_id;

		var fees = jQuery(feeCheck).val();

		var cc_processing_fee = '';
		if( jQuery(feeCheck).data('require') !== '' ) {
			if( false == jQuery('#processing-fees-'+ campaign_id + '_' + rand_id ).prop( 'checked' ) ){
				var cc_processing_fee = 'require';
			}
		}

		var gift_aid = '';

		if ( jQuery( `#wc_donation_gift_aid_checkbox_${campaign_id}_${rand_id}` ).length > 0 && jQuery( `#wc_donation_gift_aid_checkbox_${campaign_id}_${rand_id}` ).is(':checked') ) {
			gift_aid = 'yes';
		}

		var tribute 		= '';
		var tribute_message = '';

		if ( jQuery( `#_hidden_tribute_${campaign_id}_${rand_id}` ).length > 0 && jQuery("input[name='wc_donation_tribute_checkbox']:checked").length && jQuery( `#_hidden_tribute_${campaign_id}_${rand_id}` ).val() != '' ) {
			tribute = jQuery( `#_hidden_tribute_${campaign_id}_${rand_id}` ).val();
		}

		if( jQuery( `#wc_donation_trubte_message_${campaign_id}_${rand_id}` ).length > 0 && jQuery("input[name='wc_donation_tribute_checkbox']:checked").length && jQuery( `#wc_donation_trubte_message_${campaign_id}_${rand_id}` ).val() != '' ) {
			tribute_message = jQuery( `#wc_donation_trubte_message_${campaign_id}_${rand_id}` ).val();
		}

		var wps_sfw_sub_number  		= '';
		var wps_sfw_sub_interval 		= '';
		var wps_sfw_sub_exp_number 		= '';
		var wps_sfw_sub_exp_interval 	= '';

		if (jQuery('.wc_donation_subscription_table').length) {
			var wps_sfw_sub_number = jQuery('.wc_donation_subscription_table').find('.wps_sfw_subscription_number').val();
			var wps_sfw_sub_interval = jQuery('.wc_donation_subscription_table').find('.wps_sfw_subscription_interval').val();
			var wps_sfw_sub_exp_number = jQuery('.wc_donation_subscription_table').find('.wps_sfw_subscription_expiry_number').val();
			var wps_sfw_sub_exp_interval = jQuery('.wc_donation_subscription_table').find('.wps_sfw_subscription_expiry_interval').val();
		}



		addDonationToOrder(type, amount, min_amount, max_amount, campaign_id, is_recurring, new_period, new_length, new_interval, tracker, donation_type, cause, fees, gift_aid, tribute, tribute_message, cc_processing_fee, _this ,wps_sfw_sub_number ,wps_sfw_sub_interval ,wps_sfw_sub_exp_number, wps_sfw_sub_exp_interval, selectedLabel);
	}
);

jQuery(document).on('click', 'label.wc-label-button input[type="radio"][name="wc_label_price"], label.wc-label-button input[type="radio"][name="wc_label_cause"]', function() {

	if ( jQuery(this).is(':checked') ) {
		//alert('I clicked');
		jQuery(this).parent('.wc-label-button').siblings().removeClass('wc-active');
		jQuery(this).parent('.wc-label-button').addClass('wc-active');
	}
});

jQuery(document).on('click', 'label.wc-label-radio input[type="radio"][name="wc_donation_tribute_checkbox"]', function() {
	//debugger;
	if ( jQuery(this).is(':checked') ) {
		jQuery(this).parent().siblings('.wc_donation_trubte_name').prop('type', 'text');
		jQuery(this).parent().siblings('.wc_donation_trubte_message').prop('type', 'text');
	}
});

jQuery(document).on('copy paste keyup click', '.wc_donation_trubte_name', function() {
	var tribute_name = jQuery(this).val();
	var tribute_check_value = jQuery(this).siblings('label.wc-label-radio').find('input[name="wc_donation_tribute_checkbox"]:checked').val();
	if ( jQuery.trim( tribute_name ) != '' && jQuery.trim( tribute_check_value ) != '' ) {
		jQuery(this).siblings('input[name="tribute"]').val( `${tribute_check_value} ${tribute_name}` );
	} else {
		jQuery(this).siblings('input[name="tribute"]').val('');
	}
});

jQuery(document).on('change', '.wc-label-radio input.donation-processing-fees', function(){
	var id = jQuery(this).data('id');
	var campid = jQuery(this).data('camp');
	var procFees = jQuery(this).val();
	var wcdonationprice = jQuery('#wc-donation-price-'+campid).val();
	if ( wcOrderScript.donationToOrder.fees_type == 'percentage' ) {
		var donation_card_fee = wcdonationprice*(procFees/100);
	} else {
		var donation_card_fee = procFees;
	}
	var donation_summary_total = parseFloat(wcdonationprice) + parseFloat(donation_card_fee);

	if ( donation_summary_total > 0 && donation_summary_total != '' ) {
		jQuery('#wc-donation-summary-' + campid + ' .wc-donation-currency-symbol').attr('style', 'display: inline-block !important');
		jQuery('#wc-donation-summary-' + campid + ' .wc-donation-charge .wc-donation-amt').text(parseFloat(wcdonationprice).toFixed(2));
		jQuery('#wc-donation-summary-' + campid + ' .wc-donation-fee-summary .wc-donation-amt').text(parseFloat(donation_card_fee).toFixed(2));
		jQuery('#wc-donation-summary-' + campid + ' .wc-donation-summary-total .wc-donation-amt').text(parseFloat(donation_summary_total).toFixed(2));
	}
	if ( jQuery(this).is(':checked') ) {
		jQuery('#wc-donation-summary-'+campid).show();
		jQuery('#wc-donation-' + id).val(procFees);
	}
	else {
		jQuery('#wc-donation-summary-'+campid).hide();
		jQuery('#wc-donation-' + id).val(undefined);
	}
});
jQuery(document).on("click", "ul.causes-dropdown .init", function() {
	jQuery(this).parent().children('li:not(.init)').toggle();
	jQuery(this).parent().toggleClass('active');
	if ( jQuery(this).parent().hasClass('active') ) {
		if ( jQuery(this).parents('.wc-donation-container').length ) {
			var i = (75 * jQuery(this).parent().children('li:not(.init)').length);
			jQuery(this).parents('.wc-donation-cause').css('margin-bottom', `${i}px`);
		}
	} else {
		jQuery(this).parents('.wc-donation-cause').css('margin-bottom', `0px`);
	}
});

jQuery(document).on("click", "ul.causes-dropdown li:not(.init)", function() {
	var allOptions = jQuery(this).parent().children('li:not(.init)');
	var id = jQuery(this).data('id');
	var causeName = jQuery(this).data('name');
	jQuery('#wc-donation-' + id).val(causeName);
	allOptions.removeClass('selected');
	jQuery(this).addClass('selected');
	jQuery(this).parent().removeClass('active');
	jQuery(this).parent().children('.init').html(jQuery(this).html());
	allOptions.toggle();
});

jQuery(document.body).mousedown(function(event) {
	var target = jQuery(event.target);
	if ( jQuery('ul.causes-dropdown').hasClass('active') && !target.parents().andSelf().is('ul.causes-dropdown')) { // Clicked outside
		jQuery('ul.causes-dropdown').removeClass( 'active' );
		jQuery('ul.causes-dropdown').children('li:not(.init)').toggle();
	}
});

// jQuery(document).on('change', '.wc-label-radio input.donation-processing-fees', function(){
// 	if(jQuery(this).is('checked')){
// 		var id = jQuery(this).data('id');
// 		jQuery('#wc-donation-' + id).val(jQuery(this).val());
// 	}
// 	else{
// 		jQuery('#wc-donation-' + id).val('');
// 	}
// });

jQuery(document).on('change copy paste click', '.wc-label-button input, .wc-label-radio input, .wc-label-select', function(){

	var id = jQuery(this).data('id');

	jQuery('#wc-donation-price-' + id).val(jQuery(this).val());
	if ( jQuery(this).val() == 'wc-donation-other-amount' ) {
		jQuery("#wc-donation-f-donation-other-value-" + id).show();
		jQuery("#wc-donation-f-donation-other-value-" + id).addClass('wc-opened');
		// jQuery("#wc-donation-f-donation-other-value-" + id).siblings('.wc-donation-tooltip').show();
		jQuery(".other-price-wrapper-" + id).show();
		jQuery(".other-price-wrapper-" + id).siblings('.wc-donation-tooltip').show();
	} else {
		jQuery("#wc-donation-f-donation-other-value-" + id).hide();
		jQuery("#wc-donation-f-donation-other-value-" + id).removeClass('wc-opened');
		// jQuery("#wc-donation-f-donation-other-value-" + id).siblings('.wc-donation-tooltip').hide();
		jQuery(".other-price-wrapper-" + id).siblings('.wc-donation-tooltip').hide();
		jQuery(".other-price-wrapper-" + id).hide();
	}
	var procFees = jQuery('#processing-fees-'+id).val();
	var wcdonationprice = jQuery('#wc-donation-price-' + id).val();
	if ( wcOrderScript.donationToOrder.fees_type == 'percentage' ) {
		var donation_card_fee = wcdonationprice*(procFees/100);
	} else {
		var donation_card_fee = procFees;
	}
	var donation_summary_total = parseFloat(wcdonationprice) + parseFloat(donation_card_fee);
	if ( donation_summary_total > 0 && donation_summary_total != '' ) {
		jQuery('#wc-donation-summary-' + id + ' .wc-donation-currency-symbol').attr('style', 'display: inline-block !important');
		jQuery('#wc-donation-summary-' + id + ' .wc-donation-charge .wc-donation-amt').text(parseFloat(wcdonationprice).toFixed(2));
		jQuery('#wc-donation-summary-' + id + ' .wc-donation-fee-summary .wc-donation-amt').text(parseFloat(donation_card_fee).toFixed(2));
		jQuery('#wc-donation-summary-' + id + ' .wc-donation-summary-total .wc-donation-amt').text(parseFloat(donation_summary_total).toFixed(2));
	}
});

jQuery(document).on('change', '._subscription_period', function(){

	var $this = jQuery(this);
	var subscription_field = $this.parents('.wc-donation-in-action').find('select[name="_subscription_length"]');
	var period = $this.val();

	jQuery.ajax({
		url: wcOrderScript.donationToOrder.ajaxUrl,
		type: "POST",
		dataType: "json",
		data: {
			action: 'wc_donation_get_sub_length_by_sub_period',
			period: period,
			nonce: wcOrderScript.donationToOrder.nonce
		},
		beforeSend: function () {
			jQuery(subscription_field).css({'pointer-events': 'none', 'opacity': '0.5'});
		},
		success: function (response) {

			if ( response.range != '' ) {
				jQuery(subscription_field).html('');
				jQuery.each(response.range, function(index, val) {
					jQuery(subscription_field).append('<option value="'+ index +'">'+ val +'</option>');
				} );

				jQuery(subscription_field).removeAttr('style');
			}
		}
	});

});

jQuery(function(jQuery){

	jQuery(".donor-lists .give-grid__item").slice(0, 12).css('display', 'flex');
	jQuery(".anonymous-donor-lists .give-grid__item").slice(0, 12).css('display', 'flex');
	jQuery(".global-donor-lists .give-grid__item").slice(0, 12).css('display', 'flex');
	jQuery(".leaderboard-donor-lists .give-grid__item").slice(0, 12).css('display', 'flex');
	jQuery(".give-donor__load_more").on('click', function (e) {
		e.preventDefault();
		jQuery(this).siblings('.give-grid').find(".give-grid__item:hidden").slice(0, 4).css('display', 'flex');
		if (jQuery(this).siblings('.give-grid').find(".give-grid__item:hidden").length == 0) {
			jQuery(this).fadeOut(700);
		}
	});

	jQuery('input[name="wc_donation_tribute_checkbox"]').click(function(){
		var $radio = jQuery(this);
		if ($radio.data('waschecked') == true) {
			$radio.prop('checked', false);
			jQuery(".wc_donation_trubte_name").prop('type', 'hidden');
			jQuery(".wc_donation_trubte_message").prop('type', 'hidden');
			$radio.data('waschecked', false);
		} else {
			$radio.data('waschecked', true);
		}
		$radio.siblings('input[name="wc_donation_tribute_checkbox"]').data('waschecked', false);
	});
});

function dispaly_socialshare_links( $ ) {
	jQuery( document ).ready( function() {
		// Show the popup when the button is clicked
		jQuery( document ).on( 'click', '#wc-donation-social-share', function() {
			jQuery('#social-share-popup').css({
				'display': 'flex',
				'justify-content': 'center',
				'align-items': 'center',
				'overflow': 'hidden'
			}).show();
		});

		// Hide the popup when the close button is clicked
		jQuery('.close').click(function() {
			jQuery('#social-share-popup').hide();
		});

		// Hide the popup when clicking outside of the popup content
		jQuery(window).click(function(event) {
			if (jQuery(event.target).is('#social-share-popup')) {
				jQuery('#social-share-popup').hide();
			}
		});

		// Copy link to clipboard
		jQuery('#copy-link').click(function(e) {
			e.preventDefault();
			const url = jQuery(this).data('url');
			const tempInput = jQuery('<input>');
			jQuery('body').append(tempInput);
			tempInput.val(url).select();
			document.execCommand('copy');
			tempInput.remove();
			alert('Link copied to clipboard!');
		});

		// Trigger print dialog with custom content
		jQuery('#print-poster').click(function(e) {
			e.preventDefault();

			// Create a new window
			var printWindow = window.open('', '_blank');

			// Get the content to print
			var printContents = document.getElementById('print-section').innerHTML;

			// Write the content into the new window
			printWindow.document.write('<html><head><title>Print Poster</title>');
			printWindow.document.write('</head><body>');
			printWindow.document.write(printContents);
			printWindow.document.write('</body></html>');

			// Close the document to trigger the print dialog
			printWindow.document.close();

			// Wait for the content to be fully loaded
			printWindow.onload = function() {
				printWindow.print();
				printWindow.close();
			};
		});

	});
}

document.addEventListener("DOMContentLoaded", function () {
	if (jQuery('.donation-is-recurring').length) {
		const recurringCheckbox = document.querySelector(".donation-is-recurring");
		const donationSubscriptionDiv = document.querySelector(".donation_subscription");

		// Function to toggle visibility based on checkbox state
		function toggleSubscriptionVisibility() {
			if (recurringCheckbox.checked) {
				donationSubscriptionDiv.style.display = "block";
			} else {
				donationSubscriptionDiv.style.display = "none";
			}
		}

		// Run on page load in case checkbox is already checked
		toggleSubscriptionVisibility();

		// Add event listener for changes to the checkbox
		recurringCheckbox.addEventListener("change", toggleSubscriptionVisibility);
	}
});

document.addEventListener("DOMContentLoaded", function () {
	if (jQuery('.donation-is-recurring').length) {
		const recurringCheckbox = document.querySelector(".donation-is-recurring");
		const subscriptionOptions = document.querySelectorAll(".subscription-options");

		// Function to toggle visibility based on checkbox state
		function toggleSubscriptionOptions() {
			const display = recurringCheckbox.checked ? "table-row" : "none";
			subscriptionOptions.forEach(row => {
				row.style.display = display;
			});
		}

		// Run on page load to set initial state
		toggleSubscriptionOptions();

		// Add event listener for changes to the checkbox
		recurringCheckbox.addEventListener("change", toggleSubscriptionOptions);
	}
});

jQuery(document).ready(function($) {
	var allowSubmit = true; // Flag to control AJAX submission

	jQuery('#wc-donation-f-submit-donation').on('click', function(e) {
		var subscriptionNumber = parseInt(jQuery('#wps_sfw_subscription_number').val(), 10);
		var expiryNumber = parseInt(jQuery('#wps_sfw_subscription_expiry_number').val(), 10);
		if (jQuery('#wps_sfw_subscription_number').length) {
			// Check if the checkbox is checked and subscription number is empty
			if (jQuery('.donation-is-recurring').is(':checked')) {

				if (isNaN(subscriptionNumber) || subscriptionNumber === '') {
					alert('Please enter the number of subscriptions per interval.');
					allowSubmit = false;
					e.preventDefault(); // Prevent form submission
					return; // Exit the function to avoid further checks
				}

			}
			// Validate if number is positive
			if (subscriptionNumber < 1 || expiryNumber < 1) {
				alert('Interval cannot be negative.');
				allowSubmit = false;
				e.preventDefault(); // Prevent form submission
				return; // Exit the function to avoid further checks
			}
			// Check if the subscription number is greater than the expiry number
			if (subscriptionNumber > expiryNumber) {
				alert('Subscriptions Per Interval must not be greater than Subscriptions Expiry Interval.');
				allowSubmit = false; // Set flag to false to prevent AJAX
				e.preventDefault(); // Prevent form submission
			} else {
				allowSubmit = true; // Allow AJAX if condition is met
			}
		}
	});

	// Intercept AJAX request if necessary
	jQuery(document).ajaxSend(function(event, jqxhr, settings) {
		if (!allowSubmit) {
			jqxhr.abort(); // Abort the AJAX request
			jQuery('.wc-donation-loader-css').hide(); // Hide loader if any
		}
	});
});


jQuery(document).ready(function($) {
	jQuery('#wps_sfw_subscription_interval').on('change', function() {
		var selectedInterval = jQuery(this).val();

		// Clear the Expiry Interval options
		jQuery('#wps_sfw_subscription_expiry_interval').empty();

		// Add the selected interval as the only option in Expiry Interval
		jQuery('#wps_sfw_subscription_expiry_interval').append('<option value="' + selectedInterval + '">' + selectedInterval.charAt(0).toUpperCase() + selectedInterval.slice(1) + '(s)</option>');
	});

	// Trigger the change event on page load to set the initial state
	jQuery('#wps_sfw_subscription_interval').trigger('change');
});