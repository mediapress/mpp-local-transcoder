// phpcs:disable
jQuery(document).ready(function($){
    var ajax_url  = MPPLocalTranscoder.ajaxurl;
    var $btn      = $('input#mpplt-bulk-process-btn');
    var $notifier = $('#mpplt-notifier');

    $btn.click(function (e) {
        e.preventDefault();

        $btn.text('Starting...');
        $btn.attr('disabled', true);

        addQueueVideo();

        return false;
    });

    function addQueueVideo() {
        $.post(
            ajax_url,
            $('form#mpplt-bulk-process-form').serialize(),
            function (response) {

                if ( response.success ) {
                    if ( response.data.remaining_items > 0 ) {
                        updateState( response.data.remaining_items );
                        addQueueVideo();
                        return;
                    } else {
                        notifyComplete(response.data.message);
                        $btn.attr('disabled', false );
                    }
                } else {
                    $btn.text('Stopped: Resume');
                    $btn.attr('disabled', false );
                }
            },
            'json'
        );
    }

    function notifyComplete(message) {
        $notifier.text(message);
        $btn.text('All done');
    }

    function updateState( count ) {
        $btn.text('Remaining :(' + count + ')');
    }
});
// phpcs:enable