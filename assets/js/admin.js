'use strict';

//import Cookies from 'js-cookie/src/js.cookie';
import 'datatables.net';
import 'datatables.net-dt';
//import 'datatables.net-buttons';
//import 'datatables.net-buttons-dt';
//import 'datatables.net-buttons-bs4';
import 'bootstrap-datepicker';
import 'chosen-js';
import 'jquery-validation'; 
import toastr from 'toastr';
//import * as JSZip from 'jszip';
import 'select2';
//import pdfMake from 'pdfmake/build/pdfmake';
//import pdfFonts from "pdfmake/build/vfs_fonts";

//pdfMake.vfs = pdfFonts.pdfMake.vfs;

window.toastr = toastr;
window.toastr.options={closeButton:!0,debug:!1,progressBar:!1,positionClass:"toast-top-right",onclick:null,showDuration:"300",hideDuration:"1000",timeOut:"15000",extendedTimeOut:"1000",showEasing:"swing",hideEasing:"linear",showMethod:"fadeIn","progressBar": true,hideMethod:"fadeOut"};

let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            let lazyImage = entry.target;
            lazyImage.src = lazyImage.dataset.src;
            lazyImage.classList.remove("lazy");
            lazyImageObserver.unobserve(lazyImage);
        }
    });
});


$(() => {
    $('.js-select2').select2();
    
    // // datatables.net
    // $('#datatable').DataTable({
    //     keys: true
    // }); 

    $('body').on('change', 'input[type=file]', function(e) {        
        // image preview
        let reader = new FileReader();
        reader.onload = (e) => {
            $("#preview").attr("src", e.target.result);
        };
        reader.readAsDataURL(e.target.files[0]);  
    });

    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd',
    });

    $("select.chosen").chosen({
        'width':'100%',
        'white-space':'nowrap', 
        disable_search_threshold: 10,
        no_results_text: "Oops, nothing found!",
      });

    $('input').on('input', function() {
        $(this).parent().find('.invalid-feedback').remove();
        $(this).removeClass('is-invalid');
        $(".alert").remove();
    });

    $("body").append("<div class='spinner'><div class='sk-folding-cube'><div class='sk-cube1 sk-cube'></div><div class='sk-cube2 sk-cube'></div><div class='sk-cube4 sk-cube'></div><div class='sk-cube3 sk-cube'></div></div></div>");
    
    $(".floatDown").on('click', function() {
        window.scrollTo(0,document.body.scrollHeight);
    })

    $(".floatUp").on('click', function() {
        window.scrollTo(0,0);
    })

    // Tell our observer to observe all img elements with a "lazy" class
    var lazyImages = document.querySelectorAll('img.lazy');
    lazyImages.forEach(img => {
      lazyImageObserver.observe(img);
    });    
});