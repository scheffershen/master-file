import $ from 'jquery';
window.jQuery = $;
window.$ = $;
import 'popper.js';
import 'bootstrap';
import 'jquery-ui/ui/effects/effect-shake.js';
import 'jquery-validation'; 

$( () => {
    if ($(".alert").length > 0 ) {
        $('input').on('input', function() {
            $(".alert").fadeOut();
          });        
        $(".card").effect("shake");
    }

    // jQuery Validation
    $("form").validate( {
        submitHandler: (form) => {
            $('button').attr("disabled", true);
            form.submit();
           },
        error: () => {
            $('button').attr("disabled", false);
       }
    });
    
});