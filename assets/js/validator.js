'use strict';

$(() => {

    // jQuery Validation
    $("form").validate( {
        submitHandler: (form) => {
               $(".spinner").show();
               form.submit();
           },
        error: () => {
               $(".spinner").hide();
       },
      ignore: ":hidden:not(.summer-note),.note-editable.panel-body"       
    });

});