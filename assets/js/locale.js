'use strict';

var formSubmitting = false;

window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
        if (formSubmitting) {
            return undefined;
        }

        var confirmationMessage = 'It looks like you have been editing something. '
                                + 'If you leave before saving, your changes will be lost.';
        
        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
};

$( () => {

    $("#reasonDiv").fadeOut();

    $('#correspondance_locale_history_isMajeur').on('change', function () {
      if($(this).prop('checked')) {
        $("#reasonDiv").fadeIn("slow");
        $("#correspondance_locale_history_reason").attr('required',true);
        //$('button').attr("disabled", true);
      } else {
        $("#reasonDiv").fadeOut("slow");
        $("#correspondance_locale_history_reason").attr('required',false);
        $("#correspondance_locale_history_reason").val("");
        //$('button').attr("disabled", false);
      }
    }); 

    // $('#correspondance_locale_history_reason').on('change', function () { 
    //     if($(this).val().length > 0) {
    //         $('button').attr("disabled", false);
    //     }
    // });

   // Page Preloader
   $('#status').fadeOut();
   $('#preloader').delay(350).fadeOut(function(){
      $('body').delay(350).css({'overflow':'visible'});
   });

    let $title = $('#correspondance_locale_history_psmf_title');
    let $client = $('#correspondance_locale_history_psmf_client');
    let $euqppvEntity = $('#correspondance_locale_history_psmf_euqppvEntity');
    let $eudravigNum = $('#correspondance_locale_history_psmf_eudravigNum');
    let $euQPPV = $('#correspondance_locale_history_psmf_euQPPV');
    let $deputyEUQPPV = $('#correspondance_locale_history_psmf_deputyEUQPPV');
    let $contactPvClient = $('#correspondance_locale_history_psmf_contactPvClient');
    let $localQPPVPays = $('#correspondance_locale_history_psmf_localQPPVPays');

    function getPsmfData() {
        let data = {};
        data[$title.attr('name')] = $('#correspondance_locale_history_psmf_title').val();
        data[$client.attr('name')] = $('#correspondance_locale_history_psmf_client').val();
        data[$euqppvEntity.attr('name')] = $('#correspondance_locale_history_psmf_euqppvEntity').val();
        data[$eudravigNum.attr('name')] = $('#correspondance_locale_history_psmf_eudravigNum').val(); 

        data[$euQPPV.attr('name')] = $('#correspondance_locale_history_psmf_euQPPV').val();
        data[$deputyEUQPPV.attr('name')] = $('#correspondance_locale_history_psmf_deputyEUQPPV').val();
        data[$contactPvClient.attr('name')] = $('#correspondance_locale_history_psmf_contactPvClient').val();   
        
        data[$localQPPVPays.attr('name')] = $('#correspondance_locale_history_psmf_localQPPVPays').val();  
        
        return data;
    }

    // euQPPV User et deputyEUQPPV Users lient à euqppvEntity
    $('body').on('change', '#correspondance_locale_history_psmf_euqppvEntity', function(){
        let $form = $('form[name=correspondance_locale_history]');
        let data = getPsmfData();
        $(".spinner").show();
        $.ajax({
            url: $('#admin_psmf_correspondance_locale3_edit').val(),
            type: $form.attr('method'),
            data: data,
            success: function(html){
                $('#correspondance_locale_history_psmf_euQPPV').next().remove();
                $('#correspondance_locale_history_psmf_deputyEUQPPV').next().remove(); 
                //$('#correspondance_locale_history_psmf_contactPvClient').next().remove(); 

                $('#correspondance_locale_history_psmf_euQPPV').replaceWith(
                    $(html).find('#correspondance_locale_history_psmf_euQPPV')
                ); 
                $('#correspondance_locale_history_psmf_deputyEUQPPV').replaceWith(
                    $(html).find('#correspondance_locale_history_psmf_deputyEUQPPV')
                );   
                // $('#correspondance_locale_history_psmf_contactPvClient').replaceWith(
                //     $(html).find('#correspondance_locale_history_psmf_contactPvClient')
                // );   

                $("select.chosen").chosen({
                    'width':'100%',
                    'white-space':'nowrap', 
                    disable_search_threshold: 10,
                    no_results_text: "Oops, nothing found!",
                  });
                
                $(".spinner").hide();        
            }, 
            error: function (jxh, textmsg, errorThrown) {
                $(".spinner").hide();
                toastr.error(jxh.status + " " + jxh.statusText, "Error");                  
            }            
        });
    });

    // contactPvClient User lie à client
    $('body').on('change', '#correspondance_locale_history_psmf_client', function(){
        let $form = $('form[name=correspondance_locale_history]');
        let data = getPsmfData();
        $(".spinner").show();
        $.ajax({
            url: $('#admin_psmf_correspondance_locale3_edit').val(),
            type: $form.attr('method'),
            data: data,
            success: function(html){
                $('#correspondance_locale_history_psmf_contactPvClient').next().remove(); 

                $('#correspondance_locale_history_psmf_contactPvClient').replaceWith(
                    $(html).find('#correspondance_locale_history_psmf_contactPvClient')
                );   

                $("select.chosen").chosen({
                    'width':'100%',
                    'white-space':'nowrap', 
                    disable_search_threshold: 10,
                    no_results_text: "Oops, nothing found!",
                  });
                
                $(".spinner").hide();        
            }, 
            error: function (jxh, textmsg, errorThrown) {
                $(".spinner").hide();
                toastr.error(jxh.status + " " + jxh.statusText, "Error");                  
            }            
        });
    });
    
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
               formSubmitting = true; 
               form.submit();
           },
        error: () => {
               $(".spinner").hide();
       },
       ignore: ":hidden:not(.summer-note),.note-editable.panel-body"  
    });

    $('select#sectionFilter').change(function() {        
        if ($(this).val() == "all") {
            $('.correspondanceLocale').show();
        } else {
            $('.correspondanceLocale').hide();
            $('.position'+$(this).val()).show();
        };
    });

    $(':submit').on('click', function() {
        $('.correspondanceLocale').show();
    });

    $("#psmfBlock").hide();

    let hide = true;
    
    $('#psmfBtn').on('click', function() {
        hide = (hide)?false:true;
        $("#psmfBlock").toggle();
        if (hide) {
            $("#psmfBtn").html('<i class="fas fa-angle-right"></i> Show');
        } else {
            $("#psmfBtn").html('<i class="fas fa-angle-down"></i> Hide');
        }
    });

    $("#psmfBlock").toggle();
});