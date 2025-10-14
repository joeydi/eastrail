$ = jQuery;

function copyToClip(el) {
	/* Get the text field */
	var copyText = document.getElementById(el);

	/* Select the text field */
	copyText.select();
	copyText.setSelectionRange(0, 99999); /*For mobile devices*/
  
	/* Copy the text inside the text field */
	document.execCommand("copy");
  
	/* Alert the copied text */
	alert("Copied the text: " + copyText.value);

	return;
}
jQuery(document).ready(function ($) {
    let mediaUploader;

    // Upload Template
    $('body').on('click', '#upload-e-card-template', function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select or Upload E-Card Template',
            button: { text: 'Use this Template' },
            multiple: false
        });

        mediaUploader.on('select', function () {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!allowedTypes.includes(attachment.mime)) {
                alert('Invalid file type. Only PNG and JPEG files are allowed.');
                return;
            }

            const remove = 'Remove';

            $('#e-card-template-ids').val(attachment.url);

            $('#e-card-template-wrapper').html(`
                <div class="e-card-template">
                    <img src="${attachment.url}" alt="E-Card Template">
                    <button type="button" class="remove-template-btn" data-template-url="${attachment.url}">
                        ${remove}
                    </button>
                </div>
            `);
        });

        mediaUploader.open();
    });

    // Remove Template
    $('body').on('click', '.remove-template-btn', function () {
        $('#e-card-template-ids').val('');
        $(this).closest('.e-card-template').remove();
    });
});


jQuery(document).ready(function(){

    $('a.feature-donation-link').on('click', function(e) {
        e.preventDefault(); // Prevent the default action

        var ajaxUrl = $(this).attr('href');
		 var params = new URLSearchParams(ajaxUrl.split('?')[1]);
        var featureDonation = params.get('feature_donation');
        var nonce = params.get('wpnonce');
        var donationId = params.get('donation_id');
        var data = {
            action: 'feature_enable_action',
            security: nonce,
			donation_id: donationId,
            feature_donation: featureDonation
        };

        $.post( ajaxUrl, data, function( response ) {
			console.log(response);
			if( 'success' == response ) {
				location.reload();
			}
        });
    });

	jQuery('select[name="wc-donation-cart-product[]"]').selectWoo();
	jQuery('select[name="wc-donation-checkout-product[]"]').selectWoo();	

	// making order block in campaign setting draggable.
	jQuery( "#donation-block-sortable" ).sortable({
		opacity: 0.6, 
        cursor: 'move', 
        tolerance: 'pointer', 
        revert: true, 
        items:'li',
        placeholder: 'state', 
        forcePlaceholderSize: true,
		update: function(event, ui) {
			jQuery.ajax({
                url: wcds.ajaxUrl,
                type: "POST",
                data: {
                	action: 'wc_donation_page_block_sorting',
					nonce: wcds.donation_reset_nonce,
                    order: jQuery( "#donation-block-sortable" ).sortable('toArray'),
                    campaign_id: $(this).data('campaign_id')
                },
                success: function (data) {
                    console.log(data);
                    //$("#test").html(data);
                }

            });
		}
	});

	jQuery( "#wc-donation-predefined-wrapper" ).sortable({
		opacity: 0.6, 
        cursor: 'move', 
        tolerance: 'pointer', 
        revert: true, 
        items:'div',
        placeholder: 'state', 
        forcePlaceholderSize: true,		
	});

	jQuery( '#wc-donation-goal-reset' ).on( 'click', function( e ) {
		e.preventDefault();		

		var campaign_id = jQuery(this).data('campaign-id');
		var data = {
			action: 'wc_donation_reset_data',
			campaign_id: campaign_id,
			nonce: wcds.donation_reset_nonce
		};

		jQuery( '#dialog-reset' ).dialog({
		    resizable: false,
		    height: "auto",
		    width: 400,
		    modal: true,
		    buttons: {
		        "Reset": function() {
		        	jQuery( this ).dialog( "close" );		        	
		          	jQuery.post( wcds.ajaxUrl, data, function ( success ) {
						if ( success ) {
							alert( 'Data resets successfully.' );
						} else {
							alert( 'Error while resetting data.' );
						}
					});
		        },
		        Cancel: function() {
		        	jQuery( this ).dialog( "close" );
		        }
		    }
	    });

	});

	jQuery( '#wc_donation_sync_data' ).click( function(e) {
		e.preventDefault();
		
		jQuery('#wc-donation-sync-result').html('');

		var data = {
			'action': 'wc_donation_sync_data'
		}

		jQuery.post( wcds.ajaxUrl, data, function( response ) {
			var obj = JSON.parse( response );
			console.log( obj );
			jQuery('#wc-donation-sync-result').html( `<ul class="first"></ul>` );
			jQuery.each( obj, function(i, v) {
				if ( v.status == 'success' ) {
					jQuery('#wc-donation-sync-result').find('ul.first').append( `<li><strong>${v.campaign_name} - (${v.campaign_id})</strong>
						<ul class="inside">
							<li>Total Donors: ${v.total_donars}</li>
							<li>Total Donation: ${v.total_donation_count}</li>
							<li>Total Amount: ${v.total_donation_amount}</li>
						</ul>
						</li>` );
				} else {
					jQuery('#wc-donation-sync-result').find('ul').append( `<li>${v.campaign_name} - (${v.campaign_id}) has failed to update.</li>` );
				}
			});
		});
	});	

	//single_capaign.php
	jQuery('.wc-donation-tablinks').on('click', function(e) {
		e.preventDefault();
		var id = jQuery(this).attr('href');
		jQuery(this).siblings('.wc-donation-tablinks').removeClass('active');
		jQuery(this).addClass('active');
		jQuery('.wc-donation-tabcontent').css('display', 'none');
		jQuery('#' + id).css('display', 'block');
		jQuery(this).siblings('input').val(id);
	});

	var firstTab = jQuery('#wc-donation-tablink').val();
	jQuery('.wc-donation-tablinks').removeClass('active');
	jQuery('a[href="'+ firstTab +'"]').addClass('active');
	jQuery('.wc-donation-tabcontent').css('display', 'none');
	jQuery('#' + firstTab).css('display', 'block');

	//campaign_settings_html.php
	jQuery('#pred-add-more').click(function(e) {
		e.preventDefault();
		// debugger;
		var parent = jQuery(this).siblings('#wc-donation-predefined-wrapper');
		var count = parent.find('.pred').length;
		//alert(count);
		var next_count = count+1;
		//alert(next_count);
		var html = '';
		html += '<div class="pred" id="pred-'+ next_count +'">';
		html += '<div class="pred-wrapper">';
		html += '<a href="#" class="dashicons dashicons-trash pred-delete"></a>';
		html += '<h4>'+ wcds.donation_level_text +'</h4>';
		html += '<div class="select-wrapper">'
					+'<label class="wc-donation-label" for="pred-amount-'+ next_count +'">'+ wcds.amount_l_text +'</label>'
					+'<input type="number" step="0.01" id="pred-amount-'+ next_count +'" Placeholder="'+ wcds.amount_p_text +'" name="pred-amount[]" value="" required>'
				+'</div>';
		html += '<div class="select-wrapper">'
					+'<label class="wc-donation-label" for="pred-label-'+ next_count +'">'+ wcds.label_l_text +'</label>'
					+'<input type="text" id="pred-label-'+ next_count +'" Placeholder="'+ wcds.label_p_text +'" name="pred-label[]" value="" required>'
				+'</div>';
		html += '</div>';
		html += '</div>';
		//alert(count);

		// jQuery(html).insertBefore(jQuery(this));
		jQuery(parent).append(html);
	});

	jQuery(document).on('click', '.pred-delete', function(e) {
		e.preventDefault();
		//alert('test');
		jQuery(this).parents('.pred-wrapper').remove();
		jQuery(this).parents('.pred').remove();
	});	

	//donation_tribute_html.php
	jQuery('#tribute-add-more').click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('#wc-donation-tribute-wrapper');
		var count = parent.find('.tribute').length;
		//alert(count);
		var next_count = count+1;
		//alert(next_count);
		var html = '';
		html += '<div class="tribute" id="tribute-'+ next_count +'">';
		html += '<div class="tribute-wrapper">';
		html += '<a href="#" class="dashicons dashicons-trash tribute-delete"></a>';
		html += '<h4>Tribute</h4>';
		html += '<div class="select-wrapper">'
					+'<label class="wc-donation-label" for="tribute-'+ next_count +'">'+ wcds.label_l_text +'</label>'
					+'<input type="text" id="tribute-'+ next_count +'" Placeholder="'+ wcds.label_p_text +'" name="tributes[]" value="">'
				+'</div>';
		html += '</div>';
		html += '</div>';
		//alert(count);

		jQuery(html).insertBefore(jQuery(this));
	});

	jQuery(document).on('click', '.tribute-delete', function(e) {
		e.preventDefault();
		//alert('test');
		jQuery(this).parents('.tribute').remove();
	});

	//donation_cause_html.php
	jQuery('#wcd-add-cause').click(function(e) {
		e.preventDefault();
		var count = 0;
		var parent = jQuery('.causes-table-body');
		jQuery('tr.no-items').remove();
		count = parent.find('tr').length;
		var cause_name = jQuery('#wc-donation-cause-name').val();
		var cause_desc = jQuery('#wc-donation-cause-desc').val();
		var cause_img = jQuery('#wc-donation-cause-thumb').val();
		if ( cause_name == '' ){
			alert('Cause Name cannot be empty');
			return false;
		}
		//alert(count);
		var next_count = count+1;
		//alert(next_count);
		var html = '';
		html += '<tr><th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-'+next_count+'">Select ' + cause_name + '</label><input id="cb-select-' + next_count + '" type="checkbox" value="' + next_count + '"></th><td class="campaign_cause_img column-campaign_cause_img has-row-actions column-primary"><img src="' + cause_img + '"><input type="hidden" class="causes_img" value="'+ cause_img +'" name="donation-cause-img[]"></td><td class="campaign_cause_name column-campaign_cause_name has-row-actions column-primary"><input type="text" class="causes_name" value="'+ cause_name +'" name="donation-cause-name[]" readonly></td><td class="campaign_cause_desc column-campaign_cause_desc has-row-actions column-primary"><input type="text" class="causes_desc" value="' + cause_desc + '" name="donation-cause-desc[]" readonly></td><td class="actions column-actions" data-colname="Actions"><a href="javascript:void(0);" class="wc-dashicons editIcon cause-edit"> <span class="dashicons dashicons-edit"></span> </a><a href="javascript:void(0);" class="wc-dashicons deleteIcon cause-delete" title="Delete"> <span class="dashicons dashicons-trash"></span></a></td></tr>';
		if( cause_img == '' ){
			var cause_img = wcds.no_cause_img;
			var html = '';
		html += '<tr><th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-'+next_count+'">Select ' + cause_name + '</label><input id="cb-select-' + next_count + '" type="checkbox" value="' + next_count + '"></th><td class="campaign_cause_img column-campaign_cause_img has-row-actions column-primary"><img src="' + cause_img + '"><input type="hidden" class="causes_img" value="'+ cause_img +'" name="donation-cause-img[]"></td><td class="campaign_cause_name column-campaign_cause_name has-row-actions column-primary"><input type="text" class="causes_name" value="'+ cause_name +'" name="donation-cause-name[]" readonly></td><td class="campaign_cause_desc column-campaign_cause_desc has-row-actions column-primary"><input type="text" class="causes_desc" value="' + cause_desc + '" name="donation-cause-desc[]" readonly></td><td class="actions column-actions" data-colname="Actions"><a href="javascript:void(0);" class="wc-dashicons editIcon cause-edit"> <span class="dashicons dashicons-edit"></span> </a><a href="javascript:void(0);" class="wc-dashicons deleteIcon cause-delete" title="Delete"> <span class="dashicons dashicons-trash"></span></a></td></tr>';
		}
		jQuery(parent).append(html);
		jQuery('#wc-donation-cause-name').val('');
		jQuery('#wc-donation-cause-desc').val('');
		jQuery('#wc-donation-cause-thumb').val('');
		var button = jQuery('a.donation-cause-thumb-rmv');
		button.next().val(''); // emptying the hidden field
		button.hide().prev().prev().addClass('button button-primary').html('Upload Thumbnail');
	});
	jQuery(document).on('click', '.cause-delete', function(e) {
		e.preventDefault();
		jQuery(this).parents('tr').remove();
		var count = 0;
		var parent = jQuery('.causes-table-body');
		count = parent.find('tr').length;
		if ( count == 0 ) {
			var html = '<tr class="no-items"><td class="colspanchange" colspan="8">No Causes found.</td></tr>';
			jQuery(parent).append(html);
		}
	});
	jQuery(document).on('click', '.delete-selected-causes', function(e) {
		e.preventDefault();
		jQuery(".causes-table-body input[type='checkbox']:checked:not('#cb-select-all-1')").closest("tr").remove();
		var count = 0;
		var parent = jQuery('.causes-table-body');
		count = parent.find('tr').length;
		if ( count == 0 ) {
			var html = '<tr class="no-items"><td class="colspanchange" colspan="8">No Causes found.</td></tr>';
			jQuery(parent).append(html);
		}
	});
	jQuery(document).on('click', '.cause-edit', function(e) {
		e.preventDefault();
		var parent = jQuery(this).parents('tr');
		var causes_input = parent.find('input.causes_name');
		var causes_desc_input = parent.find('input.causes_desc');
		if ( causes_input.attr('readonly') && causes_desc_input.attr('readonly') ) {
			jQuery(this).children('span').addClass('dashicons-saved');
			parent.find('input.causes_name').removeAttr('readonly');
			parent.find('input.causes_name').focus();
			parent.find('input.causes_desc').removeAttr('readonly');
			parent.find('img').after('<a class="donation-cause-thumb-chg" href="#" style="display:block;"> Change Thumbnail </a>');
		} else {
			jQuery(this).children('span').removeClass('dashicons-saved');
			parent.find('input.causes_name').attr('readonly',true);
			parent.find('input.causes_desc').attr('readonly',true);
			parent.find('a.donation-cause-thumb-chg').remove();
		}
	});
	jQuery('body').on('click', 'td.campaign_cause_img a.donation-cause-thumb-chg', function(event) {
	    event.preventDefault();

	    var thumb = jQuery(this).parent().find('img.causes-img');
	    var thumb_url = jQuery(this).parent().find('input.causes_img');
	    var custom_uploader = wp.media({
	        title: 'Change Thumbnail',
	        library: {
	            type: 'image'
	        },
	        button: {
	            text: 'Use this image'
	        },
	        multiple: false
	    }).on('select', function() {
	        var attachment = custom_uploader.state().get('selection').first().toJSON();
	        thumb.attr('src', attachment.url);
	        thumb_url.val(attachment.url);
	    }).open();
	});
	jQuery('body').on( 'click', '.donation-cause-thumb-upl', function(e){
 
		e.preventDefault();
 
		var button = jQuery(this),
		custom_uploader = wp.media({
			title: 'Insert Thumbnail',
			library : {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				type : 'image'
			},
			button: {
				text: 'Use this image' // button label text
			},
			multiple: false
		}).on('select', function() { // it also has "open" and "close" events
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			button.removeClass('button button-primary').html('<img width="200px" src="' + attachment.url + '">').next().val(attachment.url).next().show();
		}).open();
 
	});
 
	// on remove button click
	jQuery('body').on('click', '.donation-cause-thumb-rmv', function(e){
 
		e.preventDefault();
 
		var button = jQuery(this);
		button.next().val(''); // emptying the hidden field
		button.hide().prev().prev().addClass('button button-primary').html('Upload Thumbnail');
	});

	jQuery('.display-option').on('click', 'input[type="radio"]', function() {

		if ( jQuery('#custom_range').is(':checked') && ( ( jQuery(this).is(':checked') && jQuery(this).val() == 'both') || ( jQuery(this).is(':checked') && jQuery(this).val() == 'free-value') ) ) {
			jQuery('#wc-donation-free-value-wrapper').find('input[type="number"]').attr('required', true);
		} else {
			jQuery('#wc-donation-free-value-wrapper').find('input[type="number"]').removeAttr('required');
		}

		if ( (jQuery(this).is(':checked') && jQuery(this).val() == 'both') || (jQuery(this).is(':checked') && jQuery(this).val() == 'predefined') ) {
			jQuery('#pred-add-more').css('display', 'block');
			jQuery('#wc-donation-predefined-wrapper').find('input[type="number"]').attr('required', true);
			jQuery('#wc-donation-predefined-wrapper').find('input[type="text"]').attr('required', true);
		} else {
			jQuery('#pred-add-more').css('display', 'none');
			jQuery('#wc-donation-predefined-wrapper').find('input[type="number"]').removeAttr('required');
			jQuery('#wc-donation-predefined-wrapper').find('input[type="text"]').removeAttr('required');

		}

		if ( jQuery(this).is(':checked') && jQuery(this).val() == 'both' ) {
			jQuery('div[data-id="predefined"]').css('display', 'grid');
			jQuery('div[data-id="free-value"]').css('display', 'grid');			
			return;
		}

		if ( jQuery(this).is(':checked') ) {
			var id = jQuery(this).val();
			jQuery('div[data-id="'+ id +'"]').css('display', 'grid');
			jQuery('div[data-id="'+ id +'"]').siblings('.display-wrapper').css('display', 'none');
		}
	});

	jQuery('.custom-display-option').on('click', 'input[type="radio"]', function() {
			
		jQuery('.custom-display-option ~ .pred-wrapper > div').css('display', 'none');
		jQuery(`div[data-id="${jQuery(this).val()}"]`).css('display', 'block');

		if ( 'custom_value' == jQuery(this).val() ) {
			jQuery('#wc-donation-free-value-wrapper').find('input[type="number"]').removeAttr('required');
		} else {
			jQuery('#wc-donation-free-value-wrapper').find('input[type="number"]').attr('required', true);
		}
	});

	if (jQuery('#custom_range').is(':checked')) {
		jQuery(`div[data-id="custom_range"]`).css('display', 'block');
		jQuery(`div[data-id="custom_value"]`).css('display', 'none');
		jQuery('#wc-donation-free-value-wrapper').find('input[type="number"]').attr('required', true);
	}

	if (jQuery('#custom_value').is(':checked')) {
		jQuery(`div[data-id="custom_value"]`).css('display', 'block');
		jQuery(`div[data-id="custom_range"]`).css('display', 'none');
		jQuery('#wc-donation-free-value-wrapper').find('input[type="number"]').removeAttr('required');
	}

	if ( jQuery('#predefined').is(':checked') ) {
		jQuery('div[data-id="predefined"]').css('display', 'grid');
		jQuery('#pred-add-more').css('display', 'block');
		jQuery('div[data-id="free-value"]').css('display', 'none');
	} 
	
	if ( jQuery('#free-value').is(':checked') ) {
		jQuery('div[data-id="free-value"]').css('display', 'grid');
		jQuery('#pred-add-more').css('display', 'none');
		jQuery('div[data-id="predefined"]').css('display', 'none');
	} 
	
	if ( jQuery('#both').is(':checked') ) {
		jQuery('div[data-id="predefined"]').css('display', 'grid');
		jQuery('#pred-add-more').css('display', 'block');
		jQuery('div[data-id="free-value"]').css('display', 'grid');
	}

	jQuery('.display-option input:checked').trigger('click');

	//recurring_donation_html.php
	jQuery('#wc-donation-recurring').on('change', function(){
		if ( jQuery(this).val() == 'user' ) {
			jQuery('#wc-donation-recurring-text').css('display', 'inline-block');
		} else {
			jQuery('#wc-donation-recurring-text').css('display', 'none');
		}
        if ( jQuery(this).val() == 'enabled') {
            jQuery('#wc-donation-recurring-schedules').css('display', 'block');
        } else {
            jQuery('#wc-donation-recurring-schedules').css('display', 'none');  
        }
    });
	if ( jQuery('#wc-donation-recurring').val() == 'user' ) {
		jQuery('#wc-donation-recurring-text').css('display', 'inline-block');
	} else {
		jQuery('#wc-donation-recurring-text').css('display', 'none');
	}
    if ( jQuery('#wc-donation-recurring').val() == 'enabled' ) {
        jQuery('#wc-donation-recurring-schedules').css('display', 'block');
    } else {
        jQuery('#wc-donation-recurring-schedules').css('display', 'none');
	}


	//recurring_donation_html.php Free Version
	jQuery('#wc-free-donation-recurring').on('change', function(){
		if ( jQuery(this).val() == 'user' ) {
			jQuery('#wc-free-donation-recurring-text').css('display', 'flex');
		} else {
			jQuery('#wc-free-donation-recurring-text').css('display', 'none');
		}
        if ( jQuery(this).val() == 'enabled') {
            jQuery('.wc_donation_free_subscription').css('display', 'flex');
        } else {
            jQuery('.wc_donation_free_subscription').css('display', 'none');  
        }
    });
    if ( jQuery('#wc-free-donation-recurring').val() == 'user' ) {
			jQuery('#wc-free-donation-recurring-text').css('display', 'flex');
		} else {
			jQuery('#wc-free-donation-recurring-text').css('display', 'none');
		}
        if ( jQuery('#wc-free-donation-recurring').val() == 'enabled') {
            jQuery('.wc_donation_free_subscription').css('display', 'flex');
        } else {
            jQuery('.wc_donation_free_subscription').css('display', 'none');  
        }

	if ( 'no_of_donation' === jQuery('input[name="wc-donation-goal-display-type"]:checked').val()  ) {		
		jQuery('#blk_no_of_donation').show();
	} else if ( 'no_of_days' === jQuery('input[name="wc-donation-goal-display-type"]:checked').val() ) {
		jQuery('#blk_no_of_days').show();
	} else {
		jQuery('#blk_fixed_amount').show();
		jQuery('#blk_fixed_initial_amount').show();
	}

	jQuery('.goal-display-type label.cbx').click(function(){
		let donation_target = jQuery(this).siblings('.inp-cbx').val();
		if ( 'no_of_donation' === donation_target  ) {	
			jQuery('#blk_no_of_days').hide();
			jQuery('#blk_fixed_amount').hide();
			jQuery('#blk_fixed_initial_amount').hide();
			jQuery('#blk_no_of_donation').show();
		} else if ( 'no_of_days' === donation_target ) {
			jQuery('#blk_no_of_days').show();
			jQuery('#blk_fixed_amount').hide();
			jQuery('#blk_fixed_initial_amount').hide();
			jQuery('#blk_no_of_donation').hide();
		} else {
			jQuery('#blk_no_of_days').hide();
			jQuery('#blk_fixed_amount').show();
			jQuery('#blk_fixed_initial_amount').show();
			jQuery('#blk_no_of_donation').hide();
		}	
	});

	if ( 'enabled' === jQuery('input[name="wc-donation-goal-close-form"]:checked').val()  ) {		
		jQuery('#close_msg_text').show();
	} else {
		jQuery('#close_msg_text').hide();
	}

	jQuery('label[for="wc-donation-goal-close-form"]').click(function(){
		if ( jQuery(this).siblings('input.inp-cbx').is(':checked') ) {		
			jQuery('#close_msg_text').hide();
		} else {
			jQuery('#close_msg_text').show();
		}
	});

	jQuery("input#wc-donation-tributes").on("change", function() {
		jQuery("input[name='wc-donation-messages']").parent().parent().fadeToggle();
	});

	if( ! jQuery("input#wc-donation-tributes").is(":checked") ) {
		jQuery("input[name='wc-donation-messages']").parent().parent().hide();
	}

	jQuery("input[name='wc-donation-campaign-display-type']").on("change", function() {
		if( jQuery("input[name='wc-donation-campaign-display-type']:checked").val() == 'popup' ) {
			jQuery(".wc-donation-popup-settings").fadeIn();
		} else {
			jQuery(".wc-donation-popup-settings").fadeOut();
		}
	});

	if( jQuery("input[name='wc-donation-campaign-display-type']:checked").val() !== 'popup' ) {
		jQuery(".wc-donation-popup-settings").hide();
	}

	jQuery("input[name='wc-donation-cart-campaign-display-format']").on("change", function() {
		if( jQuery("input[name='wc-donation-cart-campaign-display-format']:checked").val() == 'button_display' ) {
			jQuery("input#wc-donation-cart-campaign-popup-button-title").parent().fadeIn();
		} else {
			jQuery("input#wc-donation-cart-campaign-popup-button-title").parent().fadeOut();
		}
	});

	if( jQuery("input[name='wc-donation-cart-campaign-display-format']:checked").val() !== 'button_display' ) {
		jQuery("input#wc-donation-cart-campaign-popup-button-title").parent().hide();
	}

	jQuery("select[name='wc-donation-cart-campaign-format-type']").on("change", function() {
		if( jQuery("select[name='wc-donation-cart-campaign-format-type']").val() == 'table' ) {
			jQuery("#wc-donation-deactivate-columns-in-table-format").fadeIn();
		} else {
			jQuery("#wc-donation-deactivate-columns-in-table-format").fadeOut();
		}
	});

	if( jQuery("select[name='wc-donation-cart-campaign-format-type']").val() == 'table' ) {
		jQuery("#wc-donation-deactivate-columns-in-table-format").show();
	} else {
		jQuery("#wc-donation-deactivate-columns-in-table-format").hide();
	}

	// jQuery( document ).on('change', '#timeType-daily', function() {
	// 	if( jQuery(this).is(':checked') ) {
	// 		jQuery("input[name='wc-donation-setTimer-time[daily][start]']").attr( 'required', true );
	// 		jQuery("input[name='wc-donation-setTimer-time[daily][end]']").attr( 'required', true );
	// 	} else {
	// 		jQuery("input[name='wc-donation-setTimer-time[daily][start]']").attr( 'required', false );
	// 		jQuery("input[name='wc-donation-setTimer-time[daily][end]']").attr( 'required', false );

	// 	}
	// });

	// jQuery(document).on('change', "#setTimer-disabled", function() {
	// 	if( jQuery(this).is(':checked') ) {
	// 		jQuery("input[name='wc-donation-setTimer-time[daily][start]']").attr( 'required', false );
	// 		jQuery("input[name='wc-donation-setTimer-time[daily][end]']").attr( 'required', false );
	// 	}
	// });

	// jQuery(document).on('change', 'input.wc_donation_time_toggle', function() {
	// 	if( jQuery(this).is(':checked') ) {
	// 		jQuery(this).parent().siblings('div').children('input').attr('required', true);
	// 	} else {
	// 		jQuery(this).parent().siblings('div').children('input').attr('required', false);
	// 	}
	// });
	// jQuery('input.wc_donation_time_toggle').each(function(i, e) {
	// 	if( e.checked ) {
	// 		e.parentElement.nextElementSibling.children[0].required = 'true';
	// 		e.parentElement.nextElementSibling.nextElementSibling.children[0].required = 'true';
	// 	}
	// });
	
});

jQuery(document).on('change', '#_subscription_period', function(){

	var $this = jQuery(this);
	var period = $this.val();

	jQuery.ajax({
		url: wcds.ajaxUrl,
		type: "POST",
		dataType: "json",
		data: {
			action: 'wc_donation_get_sub_length_by_sub_period',
			period: period,
			nonce: wcds.donation_reset_nonce
		},
		beforeSend: function () {
			jQuery('select[name="_subscription_length"]').css({'pointer-events': 'none', 'opacity': '0.5'});
		},	
		success: function (response) {
			
			if ( response.range != '' ) {
				jQuery('#_subscription_length').html('');
				jQuery.each(response.range, function(index, val) {
					jQuery('#_subscription_length').append('<option value="'+ index +'">'+ val +'</option>');
				} );

				jQuery('select[name="_subscription_length"]').removeAttr('style');
			}
		}
	});

});

$( "#wc-donation-goal-no-of-days-field" ).datepicker({ 
	dateFormat: 'd-M-yy',
	minDate: '0'
});
jQuery(document).ready(function(){
	if ( jQuery.isFunction(jQuery.fn.selectWoo)) {
		jQuery('.fee_campaign').selectWoo();
		jQuery('.wc-donation-select').selectWoo();
	}

	// init the change event
	$(document).on('change', '[data-parent] input, [data-parent] select', handleInputChange);

	//trigger on ready
	$('[data-parent] input, [data-parent] select').trigger('change');
});

function arraysAreEqual(arr1, arr2) {
  if (arr1.length !== arr2.length) {
    return false;
  }

  arr1.sort();
  arr2.sort();

  for (var i = 0; i < arr1.length; i++) {
    if (arr1[i] !== arr2[i]) {
      return false;
    }
  }

  return true;
}

function handleInputChange (event) {
	// debugger;
	// console.log(event);	
	let val = '';
	let data_parent = jQuery(this).parents('[data-parent]').data('parent');
	let This = jQuery(this);

	var myData = {
		data_parent: data_parent,	
		val: val,
		This: This
	};

	jQuery('[data-child*="'+ data_parent +'"]').each(function() {
		// debugger;

		let childs = jQuery(this).data('child');
		let childsArray;
		if ( typeof childs !== 'undefined' ) {
			childsArray = childs.split(',');
			for (var i = 0; i < childsArray.length; i++) {
		        childsArray[i] = childsArray[i].trim();
		    }
		}
		let operator = jQuery(this).data('operator');
		if ( typeof operator === 'undefined' ) {
			operator = '';
		}
		let commaSeparatedValues = jQuery(this).data('show');
		let valueArray;
		if ( '' != commaSeparatedValues ) {
			valueArray = commaSeparatedValues.split(',');
		    for (var i = 0; i < valueArray.length; i++) {
		        valueArray[i] = valueArray[i].trim();
		    }
		} else {
			valueArray[0] = val;
		}

	    // console.log(valueArray);

		if ( jQuery(This).is('input[type="radio"]') ) {
			if ( jQuery(This).is(':checked') ) {
				val = jQuery(This).val();
			}
		}

		if ( jQuery(This).is('input[type="checkbox"]') ) {
			if ( jQuery(This).is(':checked') ) {
				val = 'yes';
			} else {
				val = 'no';
			}
		}

		if ( jQuery(This).is('select') || jQuery(This).is('input[type="number"]') || jQuery(This).is('input[type="hidden"]') || jQuery(This).is('input[type="text"]') ) {			
			val = jQuery(This).val();
		}

		if ( val != '' ) {

			myData.val = val;

			if ( operator == '' ) {
				if (jQuery.inArray(val, valueArray) !== -1) { //value found in an array
					jQuery(this).show();
				} else {
					jQuery(this).hide();
				}
			} else {
				var givenValueArr = [];
				childsArray.forEach(function(child, index) {
					// debugger;
					if (jQuery('[data-parent="'+ child +'"]').find('select').length > 0) {
						givenValueArr[index] = jQuery('[data-parent="'+ child +'"]').find('select').val();
					}

					if (jQuery('[data-parent="'+ child +'"]').find('input[type="radio"]').length > 0) {
						givenValueArr[index] = jQuery('[data-parent="'+ child +'"]').find('input:checked').val();
					}

					if (jQuery('[data-parent="'+ child +'"]').find('input[type="checkbox"]').length > 0) {
						if ( jQuery('[data-parent="'+ child +'"]').find('input:checked').val() ) {
							givenValueArr[index] = 'yes';
						} else {
							givenValueArr[index] = 'no';
						}
					}

					if (jQuery('[data-parent="'+ child +'"]').find('input[type="text"]').length > 0) {
						givenValueArr[index] = jQuery('[data-parent="'+ child +'"]').find('input').val();	
					}

					if (jQuery('[data-parent="'+ child +'"]').find('input[type="hidden"]').length > 0) {
						givenValueArr[index] = jQuery('[data-parent="'+ child +'"]').find('input').val();	
					}

					if (jQuery('[data-parent="'+ child +'"]').find('input[type="number"]').length > 0) {
						givenValueArr[index] = jQuery('[data-parent="'+ child +'"]').find('input').val();
					}
				});

				if (arraysAreEqual(givenValueArr, valueArray)) { //value found in an array
					jQuery(this).show();
				} else {
					jQuery(this).hide();
				}

			}			
		}
	});

	jQuery('[data-parent="'+ data_parent +'"]').trigger('change_value', [myData]);
}

jQuery(document).ready(function($) {
    jQuery('#save-donation-settings').on('click', function () {
	    var data = jQuery('form').serialize(); // Serialize all form data
	    jQuery.ajax({
	        url: ajaxurl, // WordPress AJAX URL
	        method: 'POST',
	        data: {
	            action: 'save_wc_donation_settings',
	            settings: data,
	            nonce: wcds.nonce
	        },
	        success: function (response) {
	            // Check if the response is successful
	            if (response.success) {
	                alert(response.data.message); // Correctly access the message in the success response
	            	location.reload();
	            } else {
	                alert(response.data.message); // Handle error message
	            }
	        },
	        error: function (jqXHR, textStatus, errorThrown) {
	            alert('An error occurred: ' + textStatus);
	        }
	    });
	});
	jQuery('#cancel-donation-settings').on('click', function () {
		// Clear the Campaign dropdown
	    jQuery('#select-campaign').val('');

	    // Clear the Donation Amount field
	    jQuery('#donation-amount').val('');

	    // Clear the Products multiselect
	    jQuery('#select-products').val([]).trigger('change');

	    // Optional: Show a message indicating fields have been cleared
	    alert('Form fields have been cleared.');
	});
	jQuery(document).on('click', '.delete-campaign', function () {
	    var campaignID = jQuery(this).data('campaign-id');
	    if (confirm('Are you sure you want to delete connection between product and donation campaign?')) {
	        jQuery.ajax({
	            url: ajaxurl,
	            method: 'POST',
	            data: {
	                action: 'delete_wc_donation_campaign',
	                campaign_id: campaignID,
	                nonce: wcds.nonce
	            },
	            success: function (response) {
	                if (response.success) {
	                    alert(response.data.message);
	                    jQuery('#campaign-row-' + campaignID).remove(); // Remove the row from the table
	                } else {
	                    alert(response.data.message);
	                }
	            },
	            error: function (jqXHR, textStatus, errorThrown) {
	                alert('An error occurred: ' + textStatus);
	            }
	        });
	    }
	});
	jQuery(document).on('click', '.edit-campaign', function () {
	    var campaignID = jQuery(this).data('campaign-id');

	    jQuery.ajax({
	        url: ajaxurl, // This is the default WordPress AJAX URL
	        method: 'POST',
	        data: {
	            action: 'load_wc_donation_campaign',
	            campaign_id: campaignID,
	            nonce: wcds.nonce
	        },
	        success: function (response) {
	            if (response.success) {
	                var data = response.data;
	                // Populate the form fields
	                jQuery('#select-campaign').val(campaignID).trigger('change');
	                jQuery('#donation-amount').val(data.donation_amount);
	                jQuery('#select-products').val(data.product_ids).trigger('change'); // For multiple select
	            } else {
	                alert(response.data.message);
	            }
	        },
	        error: function () {
	            alert('An error occurred while loading campaign data.');
	        }
	    });
	});

	$('#wps_sfw_subscription_interval').on('change', function() {
        // Get the selected value
        var selectedValue = $(this).val();

        // Loop through each option in wps_sfw_subscription_expiry_interval
        $('#wps_sfw_subscription_expiry_interval option').each(function() {
            if ($(this).val() === selectedValue) {
                // Show the option if it matches the selected value
                $(this).show();
                $(this).prop('selected', true); // Set it as selected
            } else {
                // Hide the option if it doesn't match
                $(this).hide();
            }
        });
    });

    // Trigger the change event to sync on page load
    $('#wps_sfw_subscription_interval').trigger('change');

	if($('#wps_sfw_product_target_section').length > 0) {
		$('#publish').on('click', function(e) {
			var subscriptionNumber = parseInt($('#wps_sfw_subscription_number').val(), 10);
			var expiryNumber = parseInt($('#wps_sfw_subscription_expiry_number').val(), 10);

			// Check if the subscription number is greater than the expiry number
			if (subscriptionNumber > expiryNumber) {
				alert('Subscriptions Per Interval must not be greater than Subscriptions Expiry Interval.');
				allowSubmit = false; // Set flag to false to prevent AJAX
				e.preventDefault(); // Prevent form submission
			} 
		});

	}
});


