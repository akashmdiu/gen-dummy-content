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

    const BATCH_SIZE = 10;

    $('#dlg-generate-posts-form').submit(function (e) {
        e.preventDefault();

        $('#dlg-ajax-message').html('<span style="color: #0073aa;">Creating... This may take a moment. Please be patient as we finish up.</span>');


        let data = $(this).serializeArray();
        data.push({ name: 'action', value: 'dlg_generate_posts' }); // Add action parameter
        let offset = 0;

        function generatePostsBatch() {
            data.push({ name: 'offset', value: offset });

            $.post(dlg_ajax_object.ajax_url, data, function (response) {
                let result = JSON.parse(response);

                if (result.success && result.remaining > 0) {
                    offset += BATCH_SIZE;
                    generatePostsBatch(); // Call the next batch
                } else {
                    $('#dlg-ajax-message').html(result.message || 'All posts generated successfully!');
                }
            });
        }

        generatePostsBatch();
    });

    // Similar batch processing for taxonomies creation
    $('#dlg-create-taxonomies-form').submit(function (e) {
        e.preventDefault();

        $('#dlg-ajax-message-tax').html('<span style="color: #0073aa;">Creating... Please be patient as we finish up</span>');


        let data = $(this).serializeArray();
        data.push({ name: 'action', value: 'dlg_create_taxonomies' }); // Add action parameter
        let offset = 0;

        function createTaxonomiesBatch() {
            data.push({ name: 'offset', value: offset });

            $.post(dlg_ajax_object.ajax_url, data, function (response) {
                let result = JSON.parse(response);
                console.log({result});

                if (result.success && result.remaining > 0) {

                    offset += BATCH_SIZE;

                    createTaxonomiesBatch();
                } else {
                    $('#dlg-ajax-message-tax').html(result.message || 'All taxonomies created successfully!');
                }
            });
        }

        createTaxonomiesBatch();
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
