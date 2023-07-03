(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(window).load(function () {
		// region Bugsnag
		Bugsnag.start('1a9daea6c4af7704d3ea2f9fc9292059');
		// endregion

		/* region overlay */
		const overlay = $('#arcada-lgl-overlay');
		if (overlay.length) {
			overlay.insertBefore($('#wpwrap'));
		}
		/* endregion */

		/* region Wizard*/
		const startButton = $('#arcada-labs-lgl-start-button');
		const rerunLink = $('#wizard-rerun-a');
		const rerunButton = $('#wizard-rerun');
		const licenseButton = $('#arcada-labs-lgl-license-button');
		const apiKeyButton = $('#arcada-labs-lgl-api-key-button');
		const webhookButton = $('#arcada-labs-lgl-webhook-button');
		const formsButton = $('#arcada-labs-lgl-forms-button');
		const formsSkipButton = $('#arcada-labs-lgl-skip-forms-button');
		const wcProductsButton = $('#arcada-labs-lgl-wc-products-button');
		const initialSyncButton = $('#arcada-labs-lgl-initial-sync-button');
		const initialSyncSkipButton = $('#arcada-labs-lgl-initial-sync-button-skip');
		const steps = $('.arcada-labs-wizard--anchor');
		let canChangeStep = true;

		$('.copy-txt').on('click', function (event) {
			event.preventDefault();
			const link = $(this);
			const text =
				'first_name\n' +
				'last_name\n' +
				'email\n' +
				'phone_number\n' +
				'street\n' +
				'address_line_1\n' +
				'address_line_2\n' +
				'address_line_3\n' +
				'city\n' +
				'postal_code\n' +
				'country\n' +
				'state\n' +
				'gift_type_name\n' +
				'campaign_name\n' +
				'fund_name\n' +
				'category_name\n' +
				'external_id\n' +
				'received_date\n' +
				'payment_type_name\n' +
				'received_amount\n' +
				'deductible_amount\n' +
				'deposited_amount\n' +
				'external_constituent_id\n' +
				'note_text';
			navigator.clipboard.writeText(text);
			link.text('Copied!');
			setTimeout(function () {
				link.text('Copy');
			}, 3000);
		})

		$('.paste-txt').on('click', function (event) {
			event.preventDefault();
			navigator.clipboard.readText().then(clipText => {
				const input = $(this).parent().find('input').first();
				input.val(clipText);
			}).catch((e) => {
				lglErrorMsg.css('height', '38px');
				lglErrorMsg.find('span').text('You have not allowed the browser to access your clipboard.');
			});

		});

    /* region Disable/Enable inputs function */
    function disableWizardInputs() {
      $('.arcada-lgl-wizard input').each(function () {
				$(this).prop("disabled", true);
      });
      $('.arcada-lgl-wizard select').each(function () {
				$(this).prop("disabled", true);
      });
    }
    function enableWizardInputs() {
      $('.arcada-lgl-wizard input').each(function () {
				$(this).prop("disabled", false);
      });
      $('.arcada-lgl-wizard select').each(function () {
				$(this).prop("disabled", false);
      });
    }
    /* endregion*/

		rerunLink.click(function(e) {
			e.preventDefault();
			rerunButton.removeClass('lgl-hide');
		});

		rerunButton.click(function(e) {
			e.preventDefault();
			lglFormSpinner.removeClass('lgl-hide');
			licenseBtn.addClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			rerunButton.addClass('lgl-hide');
			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {
					step: 'reset',
				},
				timeout: 0,
				success: function (result) {
					window.location.reload();
				},
				error: function (error) {
					lglErrorMsg.css('height', '38px');
					lglErrorMsg.find('span').text('There was a problem with your request, please try again.');
					console.log(error);
				}
			});
		});

		steps.click(function (e) {
			e.preventDefault();
			toggleWizardStep($(this).parent().parent().find('.arcada-lgl-wizard--content'));
		})

		function toggleWizardStep(step) {
			if (canChangeStep) {
				$('.arcada-lgl-wizard--content').addClass('arcada-lgl-wizard--content-closed');
				setTimeout(() => {
					step.removeClass('arcada-lgl-wizard--content-closed');
				}, 500);
			}
		}

		function changeOverlayText(text) {
			const textElement = $('#lgl-overlay-text');
			textElement.text(text);
		}

		startButton.click(function (e) {
			e.preventDefault();
			lglFormSpinner.removeClass('lgl-hide');
			startButton.addClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Loading');
			disableWizardInputs();
			canChangeStep = false;

			const input = $('#arcada_labs_lgl_wizard_start_step');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {
					step: 'start',
					dashboardUrl: input.val()
				},
				timeout: 0,
				success: function (result) {
					if (input.val()) {
						lglSuccessMsg.find('span').text('Link saved successfully, loading next step.');
						lglSuccessMsg.css('height', '38px');
					}
					window.location.reload();
				},
				error: function (error) {
					lglErrorMsg.css('height', '38px');
					lglErrorMsg.find('span').text('There was a problem saving your information, please verify.');
					console.error(error);
					canChangeStep = true;
					enableWizardInputs();
					lglFormSpinner.addClass('lgl-hide');
					overlay.addClass('lgl-hide');
					startButton.removeClass('lgl-hide');
				},
				complete: function() {
					setTimeout(() => {
						lglErrorMsg.css('height', '0');
						lglSuccessMsg.css('height', '0');
					}, 5000);
				}
			});

		});

		licenseButton.click(function (e) {
			e.preventDefault();
			const input = $('#arcada_labs_lgl_wizard_license_step');
			lglFormSpinner.removeClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Loading');
			licenseButton.addClass('lgl-hide');
			disableWizardInputs();
			canChangeStep = false;

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {
					step: 'licenseButton',
					license: input.val()
				},
				timeout: 0,
				success: function (result) {
					// toggleWizardStep($('#arcada-lgl-wizard-step-2').find('.arcada-lgl-wizard--content').first());
					if (input.val()) {
						lglSuccessMsg.find('span').text('Your license key has been activated successfully, loading next step.');
						lglSuccessMsg.css('height', '38px');
					}
					window.location.reload();
				},
				error: function (error) {
					lglErrorMsg.css('height', '38px');
					lglErrorMsg.find('span').text('There was a problem with your license, please verify.');
					console.error(error);
					canChangeStep = true;
					enableWizardInputs();
					lglFormSpinner.addClass('lgl-hide');
					licenseButton.removeClass('lgl-hide');
					overlay.addClass('lgl-hide');
				},
				complete: function() {
					setTimeout(() => {
						lglErrorMsg.css('height', '0');
						lglSuccessMsg.css('height', '0');
					}, 5000);
				}
			});
		});

		apiKeyButton.click(function (e) {
			e.preventDefault();
			const input = $('#arcada_labs_lgl_wizard_api_key');
			disableWizardInputs();
			canChangeStep = false;

			if (input.val()) {
				lglFormSpinner.removeClass('lgl-hide');
				apiKeyButton.addClass('lgl-hide');
				overlay.removeClass('lgl-hide');
				changeOverlayText('Loading');

				$.ajax({
					type: 'POST',
					url: '/wp-json/arcada-lgl-sync/v1/wizard',
					data: {
						step: 'apiKeyButton',
						key: input.val()
					},
					timeout: 0,
					success: function (result) {
						lglSuccessMsg.find('span').text('Your API key has been saved successfully, loading next step.')
						lglSuccessMsg.css('height', '38px');
						window.location.reload();
					},
					error: function (error) {
						lglErrorMsg.css('height', '38px');
						lglErrorMsg.find('span').text('There was a problem with your API Key, please verify.');
						console.log(error);
						enableWizardInputs();
						overlay.addClass('lgl-hide');
						canChangeStep = true;
					},
					complete: function () {
						setTimeout(() => {
							lglErrorMsg.css('height', '0');
							lglSuccessMsg.css('height', '0');
						}, 5000);
					}
				});
			} else {
				lglErrorMsg.css('height', '38px');
				lglErrorMsg.find('span').text('Please enter your API Key before proceeding.');
			}
		});

		webhookButton.click(function (e) {
			e.preventDefault();
			const input = $('#arcada_labs_lgl_wizard_webhook_url');
			disableWizardInputs();
			canChangeStep = false;

			if (input.val()) {
				lglFormSpinner.removeClass('lgl-hide');
				webhookButton.addClass('lgl-hide');
				overlay.removeClass('lgl-hide');
				changeOverlayText('Loading');

				$.ajax({
					type: 'POST',
					url: '/wp-json/arcada-lgl-sync/v1/wizard',
					data: {
						step: 'webhookButton',
						key: input.val()
					},
					timeout: 0,
					success: function (result) {
						canChangeStep = true;
						toggleWizardStep($('#arcada-lgl-wizard-step-4').find('.arcada-lgl-wizard--content').first());
						lglSuccessMsg.find('span').text('Your Webhook URL has been saved successfully')
						lglSuccessMsg.css('height', '38px');
					},
					error: function (error) {
						lglErrorMsg.css('height', '38px');
						lglErrorMsg.find('span').text('There was a problem with your Webhook URL, please verify.');
						console.log(error);
					},
					complete: function () {
						lglFormSpinner.addClass('lgl-hide');
						webhookButton.removeClass('lgl-hide');
						overlay.addClass('lgl-hide');
						enableWizardInputs();

						setTimeout(() => {
							lglErrorMsg.css('height', '0');
							lglSuccessMsg.css('height', '0');
						}, 5000);
					}
				});
			} else {
				lglErrorMsg.css('height', '38px');
				lglErrorMsg.find('span').text('Please enter your Webhook URL before proceeding.');
			}
		});

		formsButton.click(function (e) {
			$('.lgl-form-group-fields-container').first().remove();
			$('.lgl-form-group-fields-container').first().css('display', 'flex');

			lglFormSpinner.removeClass('lgl-hide');
			formsButton.addClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Loading');
			setTimeout(disableWizardInputs, 300);
			canChangeStep = false;
			// e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {step: 'formsButton'},
				timeout: 0,
				success: function (result) {
					console.log('result', result);
					// window.location = '/wp-admin/admin.php?page=arcada-labs-little-green-light-data-sync-settings';
				},
				error: function (error) {
					console.log('error', error);
					overlay.addClass('lgl-hide');
				},
				complete: function() {
					// nothing to see here
				}
			});
		});

		formsSkipButton.click(function (e) {
			lglFormSpinner.removeClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Loading');
			setTimeout(disableWizardInputs, 300);
			canChangeStep = false;
			// e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {step: 'formsButton'},
				timeout: 0,
				success: function (result) {
					console.log('result', result);
					canChangeStep = true;
					toggleWizardStep($('#arcada-lgl-wizard-step-5').find('.arcada-lgl-wizard--content').first());
				},
				error: function (error) {
					console.log('error', error);
					canChangeStep = true;
					overlay.addClass('lgl-hide');
				},
				complete: function() {
					enableWizardInputs();
					lglFormSpinner.addClass('lgl-hide');
					overlay.addClass('lgl-hide');
				}
			});
		});

		wcProductsButton.click(function (e) {
			const input = $('#arcada_labs_lgl_wizard_wc_payment_step');
			disableWizardInputs();
			canChangeStep = false;
			overlay.removeClass('lgl-hide');
			changeOverlayText('Loading');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {
					step: 'wcProductsButton',
					payment_type: input.val()
				},
				timeout: 0,
				success: function (result) {
					console.log('result', result);
					enableWizardInputs();
					canChangeStep = true;
					lglFormSpinner.addClass('lgl-hide');
					wcProductsButton.removeClass('lgl-hide');
					overlay.addClass('lgl-hide');
					toggleWizardStep($('#arcada-lgl-wizard-step-6').find('.arcada-lgl-wizard--content').first());
				},
				error: function (error) {
					console.log('error', error);
				},
				complete: function() {
					// nothing to see here
				}
			});
		});

		initialSyncButton.click(function (e) {
			overlay.removeClass('lgl-hide');
			changeOverlayText('Running sync, this may take several minutes');
			disableWizardInputs();
			canChangeStep = false;

			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {step: 'initialSyncButton'},
				timeout: 0,
				success: function (result) {
					lglSuccessMsg.find('span').text('Your data has been synced successfully')
					lglSuccessMsg.css('height', '38px');
					console.log(result);
				},
				error: function (error) {
					console.log(error);
					overlay.addClass('lgl-hide');
				},
				complete: function() {
					setTimeout(() => {
						window.location.reload();
					}, 5000);
				}
			});
		});

		initialSyncSkipButton.click(function (e) {
			initialSyncButton.addClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Loading');

			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/wizard',
				data: {
					step: 'initialSyncButton',
					skip: true,
				},
				timeout: 0,
				success: function (result) {
					window.location.reload();
				},
				error: function (error) {
					console.log(error);
				},
				complete: function() {
					// nothing to see here
				}
			});
		});

		/* endregion*/

		/* region Sync page variables */
		const lglFormSpinner = $('.lgl-form-sync-spinner');
		const lglConstituentSpinner = $('#lgl-constituent-sync-spinner');
		const lglTransactionSpinner = $('#lgl-transaction-sync-spinner');
		const lglFormBtn = $('#lgl-form-sync-btn');
		const lglConstituentBtn = $('#lgl-constituent-sync-btn');
		const lglTransactionBtn = $('#lgl-transaction-sync-btn');

		const lglSuccessMsg = $('.lgl-success-msg');
		const lglErrorMsg = $('.lgl-error-msg');
		const lglDismissMsg = $('.lgl-msg-dismiss');

		/* endregion */

		/* region License Activation */
		const licenseBtn = $('#license-activation-btn');
		const licenseDeactivateA = $('#license-deactivation-a');
		const licenseDeactivateBtn = $('#license-deactivation-btn');
		/* endregion */

		/* region Settings page variables */
		const lglAddMore = $('#lgl-add-form');
		/* endregion */

		/* region Sync page js */
		lglDismissMsg.each(function () {
			$(this).on('click', function (e) {
				e.preventDefault();
				$(this).parent().css('height', '-1px');
			});
		});

		lglFormBtn.click(function (e) {
			e.preventDefault();
			$(this).closest('.arcada-lgl-modal').addClass('lgl-hide');
			// lglFormSpinner.removeClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Running sync, this may take several minutes');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/sync/forms',
				data: {},
				timeout: 0,
				success: function (result) {
					lglSuccessMsg.find('span').text('From entries successfully synced')
					lglSuccessMsg.css('height', '44px');
					console.log("lglFormBtn Success");
				},
				error: function (result) {
					try {
						if (result.responseJSON.message) {
							lglErrorMsg.find('span').text(result.responseJSON.message);
						} else {
							lglErrorMsg.find('span').text('There was an error processing the sync.');
						}
					} catch (e) {
						lglErrorMsg.find('span').text('There was an error processing the sync.');
						Bugsnag.notify(e);
					}

					lglErrorMsg.css('height', '44px');
					console.log("lglFormBtn Error", result);
				},
				complete: function () {
					overlay.addClass('lgl-hide');
					console.log("lglFormBtn Completed");
				}
			});

		});

		lglConstituentBtn.click(function (e) {
			e.preventDefault();
			$(this).closest('.arcada-lgl-modal').addClass('lgl-hide');
			// lglConstituentSpinner.removeClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Running sync, this may take several minutes');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/sync/constituent',
				data: {},
				timeout: 0,
				success: function (result) {
					lglSuccessMsg.find('span').text('Users successfully synced as constituents');
					lglSuccessMsg.css('height', '44px');
					console.log("lglConstituentBtn Success");
				},
				error: function (result) {
					try {
						if (result.responseJSON.message) {
							lglErrorMsg.find('span').text(result.responseJSON.message);
						} else {
							lglErrorMsg.find('span').text('There was an error processing the sync.');
						}
					} catch (e) {
						lglErrorMsg.find('span').text('There was an error processing the sync.');
						Bugsnag.notify(e);
					}

					lglErrorMsg.css('height', '44px');
					console.log("lglConstituentBtn Error", result);
				},
				complete: function () {
					overlay.addClass('lgl-hide');
					console.log("lglConstituentBtn Completed");
				}
			});

		});

		lglTransactionBtn.click(function (e) {
			e.preventDefault();
			$(this).closest('.arcada-lgl-modal').addClass('lgl-hide');
			// lglTransactionSpinner.removeClass('lgl-hide');
			overlay.removeClass('lgl-hide');
			changeOverlayText('Running sync, this may take several minutes');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/sync/transaction',
				data: {},
				dataType: 'text',
				timeout: 0,
				success: function (result) {
					lglSuccessMsg.find('span').text('Transactions successfully synced')
					lglSuccessMsg.css('height', '44px');
					console.log("lglTransactionBtn Success");
				},
				error: function (result) {
					try {
						if (result.responseJSON.message) {
							lglErrorMsg.find('span').text(result.responseJSON.message);
						} else {
							lglErrorMsg.find('span').text('There was an error processing the sync.');
						}
					} catch (e) {
						lglErrorMsg.find('span').text('There was an error processing the sync.');
						Bugsnag.notify(e);
					}

					lglErrorMsg.css('height', '44px');
					console.log("lglTransactionBtn Error", result);
				},
				complete: function () {
					overlay.addClass('lgl-hide');
					console.log("lglTransactionBtn Completed");
				}
			});

		});
		/* endregion */

		/* region Settings page js */
		$('.lgl-form-group-fields-container').each(function () {
			addRemoveInstanceAbility($(this));
		});

		lglAddMore.on('click', function(e) {
			e.preventDefault();
			const cloneTarget = $($(this).attr('lgl-clone-target')).first();
			const cloneElement = cloneTarget.clone();
			cloneElement.removeClass('lgl-hide');
			addRemoveInstanceAbility(cloneElement);
			cloneElement.insertAfter($('.lgl-form-group-fields-container').last());

			// increment the size of the wizard content if it exists on screen
			/*if ($('.arcada-lgl-wizard--content').length) {
				const height = $('.lgl-form-group-fields-container').first().height();
				const container = cloneTarget.closest('.arcada-lgl-wizard--content');
				container.css('max-height', (container.height() + height) + 'px');
			}*/

			$('.lgl-remove').each(function () {
				$(this).removeAttr('disabled');
			});

		});

		function addRemoveInstanceAbility(element) {
			element.find('.lgl-remove').on('click', function(e) {
				e.preventDefault();
				const rmvButtons = $('.lgl-remove');
				if (rmvButtons.length === 1 ) {
					$(this).parent().find('select').each(function () {
						$(this).find('option').first().prop('selected', true);
					})
				} else {
					// const height = $('.lgl-form-group-fields-container').first().height();
					// const container = $(this).closest('.arcada-lgl-wizard--content');
					// container.css('max-height', (container.height() - height) + 'px');
					$(this).parent().remove();
				}
			});
		}

		$('.lgl-webhook-button').each(function () {
			const button = $(this);

			button.on('click', function (e) {
				e.preventDefault();
				const span = button.parent().parent().find('.lgl-hook-response').first();
				span.text('Loading...');
				$.ajax({
					type: 'POST',
					url: '/wp-json/arcada-lgl-sync/v1/sync/webhook',
					data: {},
					timeout: 0,
					success: function (result) {
						if (result.info) {
							span.text(`The info sent was ${result.info.first_name} ${result.info.last_name}, ${result.info.email}, ${result.info.phone_number}`);
						}
					},
					error: function (result) {
						console.log('HOOK ERROR', result);
						span.text('The hook failed, see log for more details');
					},
					complete: function () {
						// todo the complete
					}
				})
			})

		})

		/* endregion */

		/* region License page js */
		licenseBtn.click( function (e) {
			e.preventDefault();
			const license = $('#arcada_labs_lgl_sync_license_key').val();
			const nonce = $('#_wpnonce').val();
			console.log("ACTIVATION CLICK", nonce);
			lglFormSpinner.removeClass('lgl-hide');
			licenseBtn.addClass('lgl-hide');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/sync/activate-license',
				data: {
					license,
				},
				timeout: 0,
				success: function (result) {
					console.log('result', result);
					lglSuccessMsg.find('span').text('Your license key has been activated successfully')
					lglSuccessMsg.css('height', '44px');
				},
				error: function (result) {
					try {
						console.log('responseJSON', result);
						if (result.responseJSON.data.message) {
							lglErrorMsg.find('span').text(result.responseJSON.data.message);
						} else {
							lglErrorMsg.find('span').text('There was an unknown issue with your request, please contact the support team for help.');
						}
					} catch (e) {
						lglErrorMsg.find('span').text('There was an unknown issue with your request, please contact the support team for help.');
						Bugsnag.notify(e);
					}
					lglErrorMsg.css('height', '44px');
				},
				complete: function () {
					lglFormSpinner.addClass('lgl-hide');
					licenseBtn.removeClass('lgl-hide');
				},
			});
		});

		licenseDeactivateA.click(function(e) {
			e.preventDefault();
			licenseDeactivateBtn.removeClass('lgl-hide');
		});

		licenseDeactivateBtn.click(function (e) {
			e.preventDefault();

			lglFormSpinner.removeClass('lgl-hide');
			licenseBtn.addClass('lgl-hide');
			licenseDeactivateBtn.addClass('lgl-hide');

			$.ajax({
				type: 'POST',
				url: '/wp-json/arcada-lgl-sync/v1/sync/deactivate-license',
				timeout: 0,
				success: function (result) {
					console.log('result', result);
					lglSuccessMsg.find('span').text('Your license key has been deactivated successfully')
					lglSuccessMsg.css('height', '44px');
					window.location.reload();
				},
				error: function (result) {
					lglFormSpinner.addClass('lgl-hide');
					licenseBtn.removeClass('lgl-hide');
					try {
						console.log('responseJSON', result);
						if (result.responseJSON.data.message) {
							lglErrorMsg.find('span').text(result.responseJSON.data.message);
						} else {
							lglErrorMsg.find('span').text('There was an unknown issue with your request, please contact the support team for help.');
						}
					} catch (e) {
						lglErrorMsg.find('span').text('There was an unknown issue with your request, please contact the support team for help.');
						Bugsnag.notify(e);
					}
					lglErrorMsg.css('height', '44px');
				},
				complete: function () {
					// lglFormSpinner.addClass('lgl-hide');
					// licenseBtn.removeClass('lgl-hide');
				},
			});
		})
		/* endregion */

		/* region hide other dudes messages */
		if ($('.arcada-lgl-header').length) {
			$('div.notice.notice-error').css('display', 'none');
		}
		/* endregion*/

		/* region Tawk.to widget */
		const arcadaHeader = $('.arcada-lgl-header');
		if (arcadaHeader.length) {
			<!--Start of Tawk.to Script-->
			var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
			(function () {
				var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
				s1.async = true;
				s1.src = 'https://embed.tawk.to/624dd3e5c72df874911d76cc/1g0007efa';
				s1.charset = 'UTF-8';
				s1.setAttribute('crossorigin', '*');
				s0.parentNode.insertBefore(s1, s0);
			})();
			<!--End of Tawk.to Script-->
		}
		/* endregion */

		/* region Modal */
		$('.arcada-lgl-modal-open').on('click', function (e) {
			e.preventDefault();
			$('#' + $(this).attr('modal-target')).removeClass('lgl-hide');
		});

		$('.arcada-lgl-modal-close').on('click', function (e) {
			e.preventDefault();
			$(this).closest('.arcada-lgl-modal').addClass('lgl-hide');
		})
		/* endregion */

		/* region WC quick edit control*/
		$('#the-list').on('click', '.editinline', function() {

			const tr = $(this).closest('tr');
			const post_id = tr.attr('id').replace('post-', 'edit-');
			const edit_tr = $('#'+post_id);

			const _lgl_sync = tr.find('._lgl_sync').first().text();
			if (_lgl_sync === 'Yes') {
				edit_tr.find('input[name="_lgl_sync"]').first().attr('checked', true);
			}

			const _lgl_category = tr.find('._lgl_category').first().text();
			edit_tr.find('select[name="_lgl_category"]').first().find('option[value="' + _lgl_category + '"]').attr('selected', 'selected');

			const _lgl_campaign = tr.find('._lgl_campaign').first().text();
			edit_tr.find('select[name="_lgl_campaign"]').first().find('option[value="' + _lgl_campaign + '"]').attr('selected', 'selected');

			const _lgl_fund = tr.find('._lgl_fund').first().text();
			edit_tr.find('select[name="_lgl_fund"]').first().find('option[value="' + _lgl_fund + '"]').attr('selected', 'selected');

			const _lgl_gift_type = tr.find('._lgl_gift_type').first().text();
			edit_tr.find('select[name="_lgl_gift_type"]').first().find('option[value="' + _lgl_gift_type + '"]').attr('selected', 'selected');

		});
		/* endregion */
	})

})( jQuery );
