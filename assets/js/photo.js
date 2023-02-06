import Dropzone from 'dropzone';
import 'jquery-ui-dist/jquery-ui.js';

require('jquery-ui-touch-punch');

Dropzone.autoDiscover = false;

$(document).ready( () => {

    let $form = $('.js-photo-dropzone');

    if ($form.length) {
        $form.dropzone({
            url: $form.attr('action'),
            acceptedFiles: 'image/*',
            queuecomplete:  () => {
                setTimeout( () => {
                    window.location.reload();
                }, 200);
            }
        });
    }

});