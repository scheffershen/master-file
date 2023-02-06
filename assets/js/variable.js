'use strict';

$( () => {

    // btn pdf preview
    $('.btnPdfPreview').on('click', function() {
        $(".spinner").show();
        var correspondance = $('#variable_userHelp').val();
        $.ajax({
            url: $('#admin_template_variable_userHelp').val(),
            type: "POST",
            data: {'correspondance':correspondance},
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