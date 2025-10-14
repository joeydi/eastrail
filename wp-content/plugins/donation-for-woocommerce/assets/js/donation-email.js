jQuery(document).ready(function($) {
    $('.toggle-email-status').on('change', function() {
        const postId = $(this).data('post-id');
        const isEnabled = $(this).is(':checked') ? 'yes' : 'no';

        $.ajax({
            url: ajaxurl, // Built-in WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'update_email_status',
                post_id: postId,
                email_templates_enabled: isEnabled,
                nonce: donationEmailVars.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Email status updated successfully.');
                } else {
                    console.error('Failed to update email status:', response.data.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX error:', xhr.responseText);
            }
        });
    });
});
jQuery(document).ready(function ($) {
    const actionsDropdown = $('#actions');
    const awarenessEmailDiv = $('#donation_awareness_email');

    // Function to toggle visibility of the div based on the selected value
    function toggleAwarenessEmailDiv() {
        if (actionsDropdown.val() === 'awareness_email') {
            awarenessEmailDiv.show();
        } else {
            awarenessEmailDiv.hide();
        }
    }

    // Call the function on page load to check the current selected value
    toggleAwarenessEmailDiv();

    // Add event listener for the change event on the dropdown
    actionsDropdown.on('change', function () {
        toggleAwarenessEmailDiv();
    });
});