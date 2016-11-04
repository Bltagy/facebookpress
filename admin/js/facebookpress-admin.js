jQuery(function($) {
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

    // Select category Ajax request 
    if ($('a.fb-button').hasClass('disabled')) {
        $(document).on('click', 'a.fb-button', function(event) {
            event.preventDefault();
        });
    }

    $(document).on('change', '#choose_post_type', function(event) {
        var that = $(this);
        // event.preventDefault();
        var data = {
            'action': 'cat_select',
            'type': $(this).val() // We pass php values differently!
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajax_object.ajax_url, data, function(response) {
            var that_name = that.attr('name');
            var this_name = that_name.replace("choose_post_type", "choose_category")
            if (!response.success) {
                $('select#choose_category[name="' + this_name + '"]').html('<option value="0">No Data</option>');
                return false;
            }
            $('select#choose_category[name="' + this_name + '"]').text('');
            $.each(response.data, function(index, val) {
                $('select#choose_category[name="' + this_name + '"]').append('<option value="' + val.term_id + '">' + val.name + '</option>');
            });
            $('select#choose_category[name="' + this_name + '"]').removeAttr('disabled');
            // alert('Got this from the server: ' + response);
        });
        /* Act on the event */
    });

    /*====================================
    =            Progress bar            =
    ====================================*/

    var progressbar = $("#progressbar"),
        progressLabel = $(".progress-label");

    progressbar.progressbar({
        value: false,
        change: function() {
            progressLabel.text(progressbar.progressbar("value") + "%");
        },
        complete: function() {
            progressLabel.text("Complete!");
        }
    });

    function progress() {
        var val = progressbar.progressbar("value") || 0;

        progressbar.progressbar("value", val + 2);

        if (val < 99) {
            // setTimeout(progress, 200);
        }
    }

    setTimeout(progress, 2000);

    /*=====  End of Progress bar  ======*/

});
