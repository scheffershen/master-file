'use strict';

$(() => {
    // changer psmfPublisher button class
    $(".correspondanceLocaleMissing").each(function () {
        let psmf = $(this).data('id');
        if (parseInt($(this).text()) > 0) {
            $('#psmfPublisher'+psmf).empty();
        } else {
            $('#psmfPublisher'+psmf).removeClass('d-none').addClass('btn-purple');
        }
    });
});