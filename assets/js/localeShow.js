'use strict';

$( () => {

    $('select#sectionFilter').change(function() {        
        if ($(this).val() == "all") {
            $('.correspondanceLocale').show();
        } else {
            $('.correspondanceLocale').hide();
            $('.position'+$(this).val()).show();
        };
    });

});