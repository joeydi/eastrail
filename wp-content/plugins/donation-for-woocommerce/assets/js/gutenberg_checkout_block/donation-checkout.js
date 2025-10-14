const WCCheckoutDonation = {
    bootstraper: () => {
        instance = WCCheckoutDonation,
        campaign_id     = 0,
        step_1_passed   = false,
        step_2_passed   = false,
        form_type       = 'multi-step',

        setTimeout( () => instance.initializer(), 500 );
        
    },

    initializer: () => {
        campaign_id = +jQuery('#wc_campaign_id').val();
        jQuery('form.checkout').append(`<input type="hidden" name="campaign_id" value="${campaign_id}" />`);

        form_type = jQuery('#wc-donation-form-type').val();

        instance.recurringComponentChanged();
        instance.causeMutationObserver();
        instance.tributeMutationObserver();
        instance.handleTributeMessageChange();
        instance.SPACheckoutErrorHandling()

    },

    donationTypeChange: elem => {
        elem = jQuery(elem);
        if ( 'yes' == elem.val() ) {
            jQuery('.wc-donation-repeating').slideDown();
        } else {
            jQuery('.wc-donation-repeating').slideUp();
        }
    },

    priceInputHandle: field => {
        field       = jQuery(field);

        var value = field.val();

        var min = +field.attr('min');
        var max = +field.attr('max');

        if ( field.siblings('.amount-frame').hasClass('component-has-error') ) {
            field.siblings('.amount-frame').removeClass('component-has-error');
        } 

        if ( value < min || value > max ) {
            jQuery('.amount-frame').addClass('component-has-error');
            jQuery('.wc-donation-donate, #place_order').attr('disabled', true);
            jQuery('.wc-donation-free-amount .description').addClass('component-has-error-visible');
        } else {
            jQuery('.amount-frame').removeClass('component-has-error');
            jQuery('.wc-donation-donate, #place_order').attr('disabled', false);
            jQuery('.wc-donation-free-amount .description').removeClass('component-has-error-visible');
            
        }

        var amount  = field.val();
        if ( jQuery(`input:radio[class="preset-amount-item"][data-amount="${amount}"]`).length && '' != amount ) {
            jQuery(`input:radio[class="preset-amount-item"][data-amount="${amount}"]`).prop('checked', true)
        } else {
            jQuery('input:radio[class="preset-amount-item"]').prop('checked', false);
        }

        if ( !( value < min || value > max ) ) {
            instance.addDonationPriceToCheckout(amount);
            instance.updateButtonText(amount);
        }
    },

    onAmountSelect: amount => {
        if ( jQuery('.wc-donation-amount-field').length ) {
            jQuery('.amount-frame').removeClass('component-has-error')
            jQuery('.wc-donation-amount-field').val(amount);
        }

        jQuery('.wc-donation-preset-value').removeClass('component-has-error'); 

        instance.addDonationPriceToCheckout(amount);
        instance.updateButtonText(amount);
    },

    validateStep: ( step, button ) => {

        button = jQuery(button);
        if ( instance.isStepValidated( step ) && instance.processStep( step, button ) ) {
            jQuery('.wc-donation-card.step-checkout .wc-donation-type-toggle').hide();
            jQuery('.wc-donation-header p.description').css('padding-bottom', '35px');
            jQuery(`.wc-donation-checkout-step-${step}.checkout-step`).removeClass('active');
            jQuery(`.wc-donation-checkout-step-${step+1}.checkout-step`).addClass('active');
        }
    },

    disableButton: button => {
        button.attr('disabled', true);
        button.find('.button-text').hide();
        button.find('.button-loading').show();
    },

    enableButton: button => {
        button.attr('disabled', false);
        button.find('.button-text').show();
        button.find('.button-loading').hide();
    },

    isStepValidated: step => {
        let passed = false;

        if ( 1 == step ) {
            var required_fields = [ 'wc-donation-price' ]; // field name attribute
            step_1_passed = required_fields.every( field_name => {
                if ( jQuery(`[name="${field_name}"]`).val() == '' || jQuery(`[name="${field_name}"]`).val() == '0' || typeof jQuery(`[name="${field_name}"]`).val() == "undefined"
            ) {
                    instance.showErrors( step, field_name );
                    return false;
                }
                return true;
            } );
            
            passed = step_1_passed;

        } else if( 2 == step ) {
            var required_fields = jQuery(`.checkout-step[data-step="${step}"] input[aria-required="true"], .checkout-step[data-step="${step}"] select[aria-required="true"], .checkout-step[data-step="${step}"] p.validate-required input, .checkout-step[data-step="${step}"] p.validate-required select`); // woocommerce checkout fields html
            if ( form_type == 'single-page' ) {
                var required_fields = jQuery('.wc-donation-card.single-page-checkout input[aria-required="true"], .wc-donation-card.single-page-checkout select[aria-required="true"]'); // woocommerce checkout fields html
            }
            required_fields.each( function( index, field ) {
                if ( jQuery(field).val() == '' || jQuery(field).val() == '0' ) {
                    instance.showErrors( step, jQuery(field).attr('name') );
                    step_2_passed = false;
                    return false;
                }
                step_2_passed = true;
            });
            passed = step_2_passed;
        }
        return passed;
    },

    processStep: ( step, button ) => {
        if ( 1 == step ) {
            return true;
        } else if ( 2 == step ) {
            return true;
        }
    },

    showErrors: ( step, error_field_name ) => {
        if ( 'wc-donation-price' == error_field_name ) {
            instance.showPriceError();
        }
        if ( 'single-page' == form_type ) {
            jQuery('.wc-donation-card.single-page-checkout').addClass('campaign-has-errors');
            jQuery(`[name="${error_field_name}"]`).parents('p').addClass('woocommerce-invalid');
            setTimeout( () => jQuery('.wc-donation-card.single-page-checkout').removeClass('campaign-has-errors'), 375 );
        } else {
            jQuery(`[data-step="${step}"]`).parents('.wc-donation-card').addClass('campaign-has-errors');
            jQuery(`[name="${error_field_name}"]`).parents('p').addClass('woocommerce-invalid');
            setTimeout( () => jQuery(`[data-step="${step}"]`).parents('.wc-donation-card').removeClass('campaign-has-errors'), 375 );
        }
    },

    showPriceError: () => {
        var free_amount     = jQuery('.wc-donation-free-amount');

        if ( free_amount.length ) {
            jQuery('.amount-frame').addClass('component-has-error');
        } else {
            jQuery('.wc-donation-preset-value').addClass('component-has-error');
        }

    },

    handleCoverCardFees: cb => {
        instance.block_donation_checkout();
        var id = jQuery(cb).attr('id');
        if ( jQuery(`input[name="${id}"]`).length === 0 ) {
            jQuery(cb).parents('.wc-donation-body').find('form.checkout').append(`<input type="hidden" name="${id}" value="${jQuery(cb).prop('checked')}" />`);
        } else {
            jQuery(`input[name="${id}"]`).val(jQuery(cb).prop('checked'));
        }
        jQuery(document.body).trigger('update_checkout');
        instance.unblock_donation_checkout();
    },

    updateButtonText: ( amount ) => {
        jQuery( '.wc-donation-donate-btn .wc-donation-donate span.amount' ).text(accounting.formatMoney( amount ));
    },

    stepBack: elem => {
        elem = jQuery(elem);
        var step = +elem.parents('.checkout-step.active').data('step');
        elem.parents('.checkout-step.active').removeClass('active');
        jQuery(`.checkout-step[data-step="${step-1}"]`).addClass('active');

        if ( 2 == step ) {
            jQuery('.wc-donation-card.step-checkout .wc-donation-type-toggle').show();
            jQuery('.wc-donation-header p.description').css('padding-bottom', '0');
        }

    },

    block_donation_checkout: () => {
        jQuery(".wc-donation-card").block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        jQuery( '#place_order' ).attr( 'disabled', 'disabled' );
    },
    
    unblock_donation_checkout: () => {
        setTimeout(function () {  
            jQuery(".wc-donation-card").unblock( { fadeOut: 0 } );
            jQuery( '#place_order' ).removeAttr( 'disabled' );
        }, 1500);
    },

    addDonationPriceToCheckout: ( amount ) => {
        if ( jQuery('form.checkout input[name="wc-donation-price"]').length === 0 ) {
            jQuery('.wc-donation-body').find('form.checkout').append(`<input type="hidden" name="wc-donation-price" value="${amount}" />`);
        } else {
            jQuery(`form.checkout input[name="wc-donation-price"]`).val(amount);
        }
        jQuery(document.body).trigger('update_checkout');
    },

    recurringComponentChanged: () => {
        jQuery('input[name="is_recurring"], #_subscription_period_interval, ._subscription_period, #_subscription_length').on('change', e => {
            instance.handleRepeatingSelection();
        });
    },

    handleRepeatingSelection: () => {
        let interval    = '1';
        let period      = 'day';
        let length      = '1';
        var isRecurring = jQuery('input[name="is_recurring"]:checked').val();

        if ( 'yes' == isRecurring ) {
            interval    = jQuery('#_subscription_period_interval').val();
            period      = jQuery('._subscription_period').val();
            length      = jQuery('#_subscription_length').val();
        }

        if ( jQuery('.donor-details form.checkout').find('#wc_donation_is_checkout_campaign_recurring').length === 0 ) {
            jQuery('.donor-details form.checkout').append(`
                <input type="hidden" name="wc_donation_is_checkout_campaign_recurring" id="wc_donation_is_checkout_campaign_recurring" value="${isRecurring}" />
                <input type="hidden" name="wc_donation_subscription_period_interval" id="wc_donation_subscription_period_interval" value="${interval}" />
                <input type="hidden" name="wc_donation_subscription_period" id="wc_donation_subscription_period" value="${period}" />
                <input type="hidden" name="wc_donation_subscription_length" id="wc_donation_subscription_length" value="${length}" />
            `);
        } else {
            jQuery('#wc_donation_is_checkout_campaign_recurring').val(isRecurring);
            jQuery('#wc_donation_subscription_period_interval').val(interval);
            jQuery('#wc_donation_subscription_period').val(period);
            jQuery('#wc_donation_subscription_length').val(length);
        }

        jQuery(document.body).trigger('update_checkout');
    },

    appendComponentToCheckoutForm: ( name, value, trigger_update_checkout = true ) => {
        if ( jQuery(`.donor-details form.checkout input[name="${name}"]`).length === 0 ) {
            jQuery('.donor-details form.checkout').append(`<input type="hidden" name="${name}" value="${value}" />`);
        } else {
            jQuery(`.donor-details form.checkout input[name="${name}"]`).val(value);
        }
        
        if ( trigger_update_checkout ) {
            jQuery(document.body).trigger('update_checkout');
        }
    },

    causeMutationObserver: () => {
        const causeField = document.querySelector('input[name="wc-donation-cause"]');
        if (causeField) {
            const observer = new MutationObserver((mutationsList) => {
                for (let mutation of mutationsList) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        instance.appendComponentToCheckoutForm('wc-donation-cause', causeField.value, false);
                    }
                }
            });
            observer.observe(causeField, { attributes: true });
        }
    },

    tributeMutationObserver: () => {
        const tributeField = document.querySelector('input[name="tribute"]');
        if (tributeField) {
            const observer = new MutationObserver((mutationsList) => {
                for (let mutation of mutationsList) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        instance.appendComponentToCheckoutForm('wc-donation-tribute', tributeField.value, false);
                    }
                }
            });
            observer.observe(tributeField, { attributes: true });
        }
    },

    handleTributeMessageChange: () => {
        jQuery('.wc-donation-tributes .wc_donation_trubte_message').on('input', function() {
            instance.appendComponentToCheckoutForm('wc-donation-tribute-message', jQuery(this).val());
        });
    },

    SPACheckoutErrorHandling: () => {
        jQuery(document.body).on( 'click', '.wc-donation-card.single-page-checkout form.checkout #place_order', function(event) {
            event.preventDefault();

            let validated = [1, 2].every( i => {
                return instance.isStepValidated(i);
            });
            if ( validated ) {
                jQuery('form.checkout').submit();
            }
        });
    }
}
document.addEventListener('DOMContentLoaded', WCCheckoutDonation.bootstraper());