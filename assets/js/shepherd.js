'use strict';

import Shepherd from 'shepherd.js';

$( () => {

  function init() {
    var shepherd = setupShepherd();
    $('#localHelp').on('click', function(e) {
      shepherd.start();
    });
  }

  function setupShepherd() {
    var shepherd = new Shepherd.Tour({
      defaultStepOptions: {
        cancelIcon: {
          enabled: true
        },
        classes: 'bg-dark text-white',
        scrollTo: {
          behavior: 'smooth',
          block: 'center'
        }
      },
      // This should add the first tour step
      steps: [
        {
          text: 'Cliquez ici pour afficher ou cacher le formulaire PSMF <i class="fas fa-angle-right"></i>',
          attachTo: {
            element: '#psmfBtn',
            on: 'left'
          },
          buttons: [
            {
              action: function() {
                return this.cancel();
              },
              secondary: true,
              text: 'Exit'
            },
            {
              action: function() {
                return this.next();
              },
              text: 'Next'
            }
          ],
          id: 'welcome'
        }
      ],
      useModalOverlay: true
    });

    // These steps should be added via `addSteps`
    const steps = [
       {
         text: 'Valeurs du correspondant client filtre par section',
         attachTo: {
           element: '#sectionFilter',
           on: 'top'
         },
         buttons: [
           {
             action: function() {
               return this.back();
             },
             secondary: true,
             text: 'Back'
           },
           {
             action: function() {
               return this.next();
             },
             text: 'Next'
           }
         ],
         id: 'including'
       }
    //   {
    //     title: 'Creating a Shepherd Tour',
    //     text: 'Creating a Shepherd tour is easy. too! ' + 'Just create a \`Tour\` instance, and add as many steps as you want.',
    //     attachTo: {
    //       element: '.hero-example',
    //       on: 'right'
    //     },
    //     buttons: [
    //       {
    //         action: function() {
    //           return this.back();
    //         },
    //         secondary: true,
    //         text: 'Back'
    //       },
    //       {
    //         action: function() {
    //           return this.next();
    //         },
    //         text: 'Next'
    //       }
    //     ],
    //     id: 'creating'
    //   },
    //   {
    //     title: 'Attaching to Elements',
    //     text: 'Your tour steps can target and attach to elements in DOM (like this step).',
    //     attachTo: {
    //       element: '.hero-example',
    //       on: 'left'
    //     },
    //     buttons: [
    //       {
    //         action: function() {
    //           return this.back();
    //         },
    //         secondary: true,
    //         text: 'Back'
    //       },
    //       {
    //         action: function() {
    //           return this.next();
    //         },
    //         text: 'Next'
    //       }
    //     ],
    //     id: 'attaching'
    //   }
    ];

    shepherd.addSteps(steps);

    return shepherd;
  }

  function ready() {
    if (document.attachEvent ? document.readyState === 'complete' : document.readyState !== 'loading') {
      init();
    } else {
      document.addEventListener('DOMContentLoaded', init);
    }
  }

  ready();
});