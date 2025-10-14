jQuery(document).ready(function($) {
    function media_upload(button_class) {
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;

        $('body').on('click', button_class, function(e) {
            var button_id = '#' + $(this).attr('id');
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(button_id);
            _custom_media = true;

            wp.media.editor.send.attachment = function(props, attachment) {
                if (_custom_media) {
                    $('#category-image-id').val(attachment.id);
                    $('#category-image-wrapper').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:100%;"/>');
                } else {
                    return _orig_send_attachment.apply(button_id, [props, attachment]);
                }
            };

            wp.media.editor.open(button);
            return false;
        });
    }

    media_upload('#upload-category-image');

    $('body').on('click', '#remove-category-image', function() {
        $('#category-image-id').val('');
        $('#category-image-wrapper').html('');
    });
});
