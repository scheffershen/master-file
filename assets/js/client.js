'use strict';

$( () => {

    $("#reasonDiv").fadeOut();

    $('#client_edit_isMajeur').on('change', function () {
      if($(this).prop('checked')) {
        $("#reasonDiv").fadeIn("slow");
        $("#client_edit_reason").attr('required',true);
        //$('button').attr("disabled", true);
      } else {
        $("#reasonDiv").fadeOut("slow");
        $("#client_edit_reason").attr('required',false);
        $("#client_edit_reason").val("");
        //$('button').attr("disabled", false);
      }
    }); 

    // $('#client_edit_reason').on('change', function () { 
    //     if($(this).val().length > 0) {
    //         $('button').attr("disabled", false);
    //     }
    // });
    
    // image preview
    $('#client_logo_file').on('change', function(e) {
      var fileName = e.target.files[0].name;
      console.log(fileName);
      var reader = new FileReader();
      reader.onload = function(e) {
        // get loaded data and render thumbnail.
        if (document.getElementById("preview")) {
			document.getElementById("preview").src = e.target.result;
        } else {
        	$('fieldset').before('<div class="form-group"><img src="https://placehold.it/600x337" id="preview" class="img-fluid"/></div>');
        	document.getElementById("preview").src = e.target.result;
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