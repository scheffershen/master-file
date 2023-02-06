'use strict';

$( () => {

    if ($('#app_request_method').val() == "GET" ) {
        $(".pvUser").hide();
    } else if ($('#user_pvUser').prop('checked')) {
        $(".pvUser").show();
        $("input.pvUser").attr('required',true);
        //$("textarea.pvUser").attr('required',true);        
    } else {
        $(".pvUser").hide();
    }
        
    $('#user_pvUser').on('click', function () {
      if($(this).prop('checked')) {
        $(".pvUser").fadeIn();
        $("input.pvUser").attr('required',true);
        //$("textarea.pvUser").attr('required',true);
        $("select.pvUser option").filter(function() {
             return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
        }).remove();
      } else {
        $(".pvUser").fadeOut();
        $("input.pvUser").attr('required',false);
        // $("textarea.pvUser").attr('required',false);
        $("select.pvUser option").filter(function() {
            return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
        }).remove();        
        $("select.pvUser").prepend("<option value='' selected='selected'></option>");
      }
    });

    // image preview
    $('#user_cv_file').on('change', function(e) {
      var fileName = e.target.files[0].name;
      console.log(fileName);
      var reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById("preview").src = e.target.result;     
      };

      // read the image file as a data URL.
      reader.readAsDataURL(this.files[0]);    
    }); 

    // jQuery Validation
    $("form").validate({
        rules: {
             "user[roles][]": {
              required: true
            },
        },
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