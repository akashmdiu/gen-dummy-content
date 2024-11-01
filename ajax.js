jQuery(document).ready(function ($) {
    const $loadingSpinner = $('<div class="dlg-loading">Generating... Please wait.</div>').css({
        display: 'none', position: 'fixed', top: '50%', left: '50%',
        transform: 'translate(-50%, -50%)', backgroundColor: 'rgba(255, 255, 255, 0.9)',
        padding: '20px', borderRadius: '8px', boxShadow: '0 0 10px rgba(0, 0, 0, 0.3)', zIndex: 1000
    }).appendTo('body');

    function postBatchProcess(action, form, messageDiv) {
        let data = form.serializeArray();
        let offset = 0;

        function processBatch() {
            data = [...data, { name: 'offset', value: offset }, { name: 'action', value: action }];
            $.post(dlg_ajax_object.ajax_url, data, function (response) {
                const result = JSON.parse(response);
                if (result.success && result.remaining > 0) {
                    offset += BATCH_SIZE;
                    processBatch();  // Call the next batch
                } else {
                    $(messageDiv).html(result.message || 'Operation completed successfully!');
                }
            });
        }
        processBatch();
    }

    $('#dlg-generate-posts-form').submit(function (e) {
        e.preventDefault();
        $('#dlg-ajax-message').html('<span style="color: #0073aa;">Creating...</span>');
        postBatchProcess('dlg_generate_posts', $(this), '#dlg-ajax-message');
    });

    $('#dlg-create-taxonomies-form').submit(function (e) {
        e.preventDefault();
        $('#dlg-ajax-message-tax').html('<span style="color: #0073aa;">Creating...</span>');
        postBatchProcess('dlg_create_taxonomies', $(this), '#dlg-ajax-message-tax');
    });

    $('#dlg-delete-content-form').on('submit', function (e) {
        e.preventDefault();
        $loadingSpinner.show();
        $.post(dlg_ajax_object.ajax_url, $(this).serialize() + '&action=dlg_delete_content&nonce=' + dlg_ajax_object.nonce,
            function (response) {
                const data = JSON.parse(response);
                $('#dlg-ajax-delete-message').html(data.success
                    ? '<div class="notice notice-success is-dismissible"><p>' + data.message + '</p></div>'
                    : '<div class="notice notice-error is-dismissible"><p>' + data.message + '</p></div>');
            }).always(() => $loadingSpinner.hide());
    });

    $('#dlg-delete-taxonomies-form').on('submit', function (e) {
        e.preventDefault();
        $loadingSpinner.show();
        $.post(dlg_ajax_object.ajax_url, $(this).serialize() + '&action=dlg_delete_taxonomy&nonce=' + dlg_ajax_object.nonce,
            function (response) {
                const data = JSON.parse(response);
                $('#dlg-ajax-delete-tax-message').html(data.success
                    ? '<div class="notice notice-success is-dismissible"><p>' + data.message + '</p></div>'
                    : '<div class="notice notice-error is-dismissible"><p>' + data.message + '</p></div>');
            }).always(() => $loadingSpinner.hide());
    });
});
