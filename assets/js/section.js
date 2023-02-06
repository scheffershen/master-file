'use strict';

$( () => {

    $("#reasonDiv").fadeOut();

    $('#section_edit_isMajeur, #section_isMajeur').on('change', function () {
      if($(this).prop('checked')) {
        $("#reasonDiv").fadeIn("slow");
        $("#section_edit_reason").attr('required',true);
        $("#section_reason").attr('required',true);
        //$('button').attr("disabled", true);
      } else {
        $("#reasonDiv").fadeOut("slow");
        $("#section_edit_reason").attr('required',false);
        $("#section_edit_reason").val("");
        $("#section_reason").attr('required',false);
        $("#section_reason").val("");        
        //$('button').attr("disabled", false);
      }
    }); 

    // btn pdf preview
    /* $('.btnPdfPreview').on('click', function() {
        $(".spinner").show();
        var correspondance = $('#section_contenu').val();
        var title = $('#section_title').val();
        $.ajax({
            url: $('#admin_template_section_contenu').val(),
            type: "POST",
            data: {'title':title, 'correspondance':correspondance},
            success: function(html){
                $(".spinner").hide();   
                var url = $('#admin_published_document_download_locale').val()+"?docfileName="+html;
                window.open(url, '_blank', 'location=yes,height=570,width=570,scrollbars=yes,status=yes');       
            }, 
            error: function (jxh, textmsg, errorThrown) {
                $(".spinner").hide();
                toastr.error(jxh.status + " " + jxh.statusText, "Error");                  
            }            
        });
    });
    */

    // jQuery Validation
    $("form").validate( {
        submitHandler: (form) => {
               $(".spinner").show();
               form.submit();
           },
        error: () => {
               $(".spinner").hide();
       },
       //ignore: ":hidden:not(.summer-note),.note-editable.panel-body"  
    });

});