'use strict';

$(() => {

    $('body').on('change', 'input#published_document_pdf_signe_pdfSigne[type=file]', function(e) {        
        // pdf preview
        let fileName = e.target.files[0].name;
        console.log(fileName);
        var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
        if (ext == "pdf" ) {
          $("#pdf-preview").html("<span class='badge badge-purple'>"+fileName+"</span>");           
        } else {
          $(this).val('');
          $("#pdf-preview").html(''); 
          toastr.error(fileName + " is not a pdf file");           
        } 

    });

});