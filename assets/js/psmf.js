'use strict';

$(() => {

    let $title = $('#psmf_title');
    let $client = $('#psmf_client');
    let $euqppvEntity = $('#psmf_euqppvEntity');
    let $eudravigNum = $('#psmf_eudravigNum');
    let $euQPPV = $('#psmf_euQPPV');
    let $deputyEUQPPV = $('#psmf_deputyEUQPPV');
    let $contactPvClient = $('#psmf_contactPvClient');
    let $localQPPVPays = $('#psmf_localQPPVPays');

    function getPsmfData() {
        let data = {};
        data[$title.attr('name')] = $('#psmf_title').val();
        data[$client.attr('name')] = $('#psmf_client').val();
        data[$euqppvEntity.attr('name')] = $('#psmf_euqppvEntity').val();
        data[$eudravigNum.attr('name')] = $('#psmf_eudravigNum').val(); 

        data[$euQPPV.attr('name')] = $('#psmf_euQPPV').val();
        data[$deputyEUQPPV.attr('name')] = $('#psmf_deputyEUQPPV').val();
        data[$contactPvClient.attr('name')] = $('#psmf_contactPvClient').val();  
        data[$localQPPVPays.attr('name')] = $('#psmf_localQPPVPays').val();  
        
        console.log(data);
        return data;

    }

    // euQPPV User et deputyEUQPPV Users lient à euqppvEntity
    $('body').on('change', '#psmf_euqppvEntity', function(){
        let $form = $('form[name=psmf]');
        let data = getPsmfData();
        $(".spinner").show();
        $.ajax({
            url: $('#admin_psmf_new_url').val(),
            type: $form.attr('method'),
            data: data,
            success: function(html){
                $('#psmf_euQPPV').next().remove();
                $('#psmf_deputyEUQPPV').next().remove(); 
                //$('#psmf_contactPvClient').next().remove(); 

                $('#psmf_euQPPV').replaceWith(
                    $(html).find('#psmf_euQPPV')
                ); 
                $('#psmf_deputyEUQPPV').replaceWith(
                    $(html).find('#psmf_deputyEUQPPV')
                );   
                // $('#psmf_contactPvClient').replaceWith(
                //     $(html).find('#psmf_contactPvClient')
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
    $('body').on('change', '#psmf_client', function(){
        let $form = $('form[name=psmf]');
        let data = getPsmfData();
        $(".spinner").show();
        $.ajax({
            url: $('#admin_psmf_new_url').val(),
            type: $form.attr('method'),
            data: data,
            success: function(html){
                //$('#psmf_euQPPV').next().remove();
                //$('#psmf_deputyEUQPPV').next().remove(); 
                $('#psmf_contactPvClient').next().remove(); 

                // $('#psmf_euQPPV').replaceWith(
                //     $(html).find('#psmf_euQPPV')
                // ); 
                // $('#psmf_deputyEUQPPV').replaceWith(
                //     $(html).find('#psmf_deputyEUQPPV')
                // );   
                $('#psmf_contactPvClient').replaceWith(
                    $(html).find('#psmf_contactPvClient')
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

    // localQPPVUM  lie à localQPPVPays
    // $('body').on('change', '#psmf_localQPPVPays', function(){
    //     let $form = $('form[name=psmf]');
    //     let data = getPsmfData();
    //     $(".spinner").show();
    //     $.ajax({
    //         url: $('#admin_psmf_new_url').val(),
    //         type: $form.attr('method'),
    //         data: data,
    //         success: function(html){
    //             $('#psmf_localQPPVUM').next().remove();   
    //             $('#psmf_localQPPVUM').replaceWith(
    //                 $(html).find('#psmf_localQPPVUM')
    //             );   

    //             $("select.chosen").chosen({
    //                 'width':'100%',
    //                 'white-space':'nowrap', 
    //                 disable_search_threshold: 10,
    //                 no_results_text: "Oops, nothing found!",
    //               });
                
    //             $(".spinner").hide();        
    //         }, 
    //         error: function (jxh, textmsg, errorThrown) {
    //             $(".spinner").hide();
    //             toastr.error(jxh.status + " " + jxh.statusText, "Error");                  
    //         }            
    //     });
    // });
      
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