import $ from 'jquery';
window.jQuery = $;
window.$ = $;
import 'popper.js';
import 'bootstrap';
import 'lazysizes';
import "metismenu";
import "./libs/slimScroll";
import "./libs/waves";

Waves.init();

$( () => {
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip();

    $("#side-menu").metisMenu();
    $('.slimscroll-menu').slimscroll({
        height: 'auto',
        position: 'right',
        size: "8px",
        color: '#9ea5ab',
        wheelStep: 5,
        touchScrollStep: 20
    });

    // Left menu collapse
    $('.button-menu-mobile').on('click', function (event) {
        event.preventDefault();
        $('body').toggleClass('sidebar-enable');
        if ($(window).width() >= 768) {
            $('body').toggleClass('enlarged');
        } else {
            $('body').removeClass('enlarged');
        }
    });    

    $(document).on('click', 'body', function (e) {
        if ($(e.target).closest('.left-side-menu, .side-nav').length > 0 || $(e.target).hasClass('button-menu-mobile')
            || $(e.target).closest('.button-menu-mobile').length > 0) {
            return;
        }

        $('body').removeClass('sidebar-enable');
        return;
    });

    // activate the menu in left side bar based on url
    $("#side-menu a").each(function () {
        var pageUrl = window.location.href.split(/[?#]/)[0];
        if (this.href == pageUrl) {
            $(this).addClass("active");
            $(this).parent().addClass("mm-active"); // add active to li of the current link
            $(this).parent().parent().addClass("mm-show");
            $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().addClass("mm-active");
            $(this).parent().parent().parent().parent().addClass("mm-show"); // add active to li of the current link
            $(this).parent().parent().parent().parent().parent().addClass("mm-active");
        }
    });

    // Topbar - main menu
    $('.navbar-toggle').on('click', function (event) {
        $(this).toggleClass('open');
        $('#navigation').slideToggle(400);
        if (localStorage['choice'] === undefined) {
            if ($(window).width() >= 768 && $(window).width() <= 1028) {
                localStorage.setItem('choice', 'open');
            } else {
                localStorage.setItem('choice', 'close');
            }
        } else {
            if (localStorage['choice'] == "open") {
                localStorage.setItem('choice', 'close');
            } else {
                localStorage.setItem('choice', 'open');
            }
        }
    });

    /* Comportement par défaut avant de cliquer sur le bouton du menu */
    if (localStorage['choice'] === undefined) {
        // in case of small size, add class enlarge to have minimal menu
        if ($(window).width() >= 768 && $(window).width() <= 1028) {
            $('body').addClass('enlarged');
        } else {
            if ($('body').data('keep-enlarged') != true) {
                $('body').removeClass('enlarged');
            }
        }   
    /* Application du choix mémorisé (menu ouvert ou fermé) */
    } else if (localStorage['choice'] === 'open') {
         $('body').removeClass('enlarged');
         $('.navbar-toggle').removeClass('open');
    } else if (localStorage['choice'] === 'close') {
        $('body').addClass('enlarged');
        $('.navbar-toggle').addClass('open');
    }  
});