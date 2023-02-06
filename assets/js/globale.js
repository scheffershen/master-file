'use strict';

$( () => {

    $("#reasonDiv").fadeOut();

    $('#correspondance_globale_history_isMajeur').on('change', function () {
      if($(this).prop('checked')) {
        $("#reasonDiv").fadeIn("slow");
        $("#correspondance_globale_history_reason").attr('required',true);
        //$('button').attr("disabled", true);
      } else {
        $("#reasonDiv").fadeOut("slow");
        $("#correspondance_globale_history_reason").attr('required',false);
        $("#correspondance_globale_history_reason").val("");
        //$('button').attr("disabled", false);
      }
    }); 

    // $('#correspondance_globale_history_reason').on('change', function () { 
    //     if($(this).val().length > 0) {
    //     }
    // });

    // image preview
    $('input[type=file]').on('change', function(e) {
      var fileName = e.target.files[0].name;
      var id = $(this).data('id');
      var reader = new FileReader();
      reader.onload = function(e) {
        // get loaded data and render thumbnail.
        if (document.getElementById("preview"+id)) {
			     document.getElementById("preview"+id).src = e.target.result;
        } else {
        	$('fieldset#field'+id).before('<div class="form-group"><img src="https://placehold.it/240x160" id="preview'+id+'" class="img-fluid"/></div>');
        	document.getElementById("preview"+id).src = e.target.result;
        }        
      };

      // read the image file as a data URL.
      reader.readAsDataURL(this.files[0]);    
    }); 

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