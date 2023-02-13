'use strict';

import $ from 'jquery';
window.jQuery = $;
window.$ = $;
import toastr from 'toastr';

window.toastr = toastr;
window.toastr.options={closeButton:!0,debug:!1,progressBar:!1,positionClass:"toast-top-right",onclick:null,showDuration:"300",hideDuration:"1000",timeOut:"15000",extendedTimeOut:"1000",showEasing:"swing",hideEasing:"linear",showMethod:"fadeIn","progressBar": true,hideMethod:"fadeOut"};

$(() => {
    $.ajax({
        method: 'post',
        url: '/fr/is_login', 
        contentType: 'application/json; charset=utf-8', 
        cache: false,
        dataType: "json",
        success: function(response){
            console.log(response.authenticated); 
            if (!response.authenticated) {
                window.location = "/fr/login";
            }     
        }, 
        error: function (jxh, textmsg, errorThrown) {
            toastr.error(jxh.status + " " + jxh.statusText, "Error");                  
        }            
    });
});