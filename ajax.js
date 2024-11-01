jQuery(document).ready(function ($) {
    // Create a loading spinner element
    const $loadingSpinner = $('<div class="dlg-loading">Generating... It will take a while; please be patient until it finishes.</div>').css({
        display: 'none',
        position: 'fixed',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        backgroundColor: 'rgba(255, 255, 255, 0.9)',
        padding: '20px',
        borderRadius: '8px',
        boxShadow: '0 0 10px rgba(0, 0, 0, 0.3)',
        zIndex: 1000,
    }).appendTo('body');

    // Handle the post generation form submission
    $('#dlg-generate-posts-form').on('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting normally

        // Show loading spinner
        $loadingSpinner.show();

        $.ajax({
            type: 'POST',
            url: dlg_ajax_object.ajax_url,
            data: $(this).serialize() + '&action=dlg_generate_posts',
            success: function (response) {
                const data = JSON.parse(response);
                $('#dlg-ajax-message').html(data.success
                    ? '<div class="notice notice-success is-dismissible"><p>' + data.message + '</p></div>'
                    : '<div class="notice notice-error is-dismissible"><p>' + data.message + '</p></div>');
            },
            error: function () {
                $('#dlg-ajax-message').html('<div class="notice notice-error is-dismissible"><p>Error generating posts.</p></div>');
            },
            complete: function () {
                // Hide loading spinner after the request is complete
                $loadingSpinner.hide();
            }
        });
    });

    // Handle the taxonomy creation form submission
    $('#dlg-create-taxonomies-form').on('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting normally

        // Show loading spinner
        $loadingSpinner.show();

        $.ajax({
            type: 'POST',
            url: dlg_ajax_object.ajax_url,
            data: $(this).serialize() + '&action=dlg_create_taxonomies',
            success: function (response) {
                const data = JSON.parse(response);
                $('#dlg-ajax-message-tax').html(data.success
                    ? '<div class="notice notice-success is-dismissible"><p>' + data.message + '</p></div>'
                    : '<div class="notice notice-error is-dismissible"><p>' + data.message + '</p></div>');
            },
            error: function () {
                $('#dlg-ajax-message-tax').html('<div class="notice notice-error is-dismissible"><p>Error creating taxonomies.</p></div>');
            },
            complete: function () {
                // Hide loading spinner after the request is complete
                $loadingSpinner.hide();
            }
        });
    });

    // Handle the delete content form submission
    $('#dlg-delete-content-form').on('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting normally

        // Show loading spinner
        $loadingSpinner.show();

        $.ajax({
            type: 'POST',
            url: dlg_ajax_object.ajax_url,
            data: $(this).serialize() + '&action=dlg_delete_content&nonce=' + dlg_ajax_object.nonce,

            success: function (response) {
                const data = JSON.parse(response);
                $('#dlg-ajax-delete-message').html(data.success
                    ? '<div class="notice notice-success is-dismissible"><p>' + data.message + '</p></div>'
                    : '<div class="notice notice-error is-dismissible"><p>' + data.message + '</p></div>');
            },
            error: function () {
                $('#dlg-ajax-delete-message').html('<div class="notice notice-error is-dismissible"><p>Error deleting content.</p></div>');
            },
            complete: function () {
                // Hide loading spinner after the request is complete
                $loadingSpinner.hide();
            }
        });
    });

    // Handle the delete taxonomy form submission
    $('#dlg-delete-taxonomies-form').on('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting normally

        // Show loading spinner
        $loadingSpinner.show();

        $.ajax({
            type: 'POST',
            url: dlg_ajax_object.ajax_url,
            data: $(this).serialize() + '&action=dlg_delete_taxonomy&nonce=' + dlg_ajax_object.nonce,
        
            success: function (response) {
                const data = JSON.parse(response);
                $('#dlg-ajax-delete-tax-message').html(data.success
                    ? '<div class="notice notice-success is-dismissible"><p>' + data.message + '</p></div>'
                    : '<div class="notice notice-error is-dismissible"><p>' + data.message + '</p></div>');
            },
            error: function () {
                $('#dlg-ajax-delete-tax-message').html('<div class="notice notice-error is-dismissible"><p>Error deleting taxonomy terms.</p></div>');
            },
            complete: function () {
                // Hide loading spinner after the request is complete
                $loadingSpinner.hide();
            }
        });
    });
});
