jQuery(document).ready(function () {
    if (typeof tinymce !== 'undefined') {
        tinymce.PluginManager.add('wc_donation_email_editor', function (editor) {
            editor.addButton('wc_donation_email_editor', {
                type: 'menubutton',
                text: 'DFW Fields',
                icon: false,
                menu: Object.keys(dfw_mce).map(function (key) {
                    return {
                        text: dfw_mce[key],
                        onclick: function () {
                            editor.insertContent(`{${key}}`);
                        },
                    };
                }),
            });
        });

        // Ensure the correct TinyMCE instance is initialized
        tinymce.init({
            selector: '#donation_editor_id', // Match the ID of your wp_editor instance
            plugins: 'wc_donation_email_editor',
            toolbar: 'formatselect | bold italic underline | wc_donation_email_editor',
            setup: function (editor) {
                // Sync editor content with the textarea
                editor.on('init', function () {
                    const content = jQuery('textarea[name="donation_email_editor"]').val();
                    this.setContent(content || '');
                });
                editor.on('change', function () {
                    tinymce.triggerSave(); // Save the content to the underlying textarea
                });
            },
        });
    }
});