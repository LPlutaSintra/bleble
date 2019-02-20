/*
 * Replace all SVG images with inline SVG
 */
define(["jquery"], function($) {
    "use strict";
    let config = {
        attributes: true,
        childList: true,
        characterData: true
    };

    let mut = new MutationObserver(function(mutations){
        mutations.forEach(function(mutation) {
            let isActive = mutation.target.classList.contains('active');
            if(isActive){;
                $(".page-header .switchers").css("z-index", "5");
            }
            else {
                $(".page-header .switchers").css("z-index", "-2");
            }
        });
    });
    mut.observe(document.querySelector(".currency .actions.options.switcher-options"),config);
    mut.observe(document.querySelector(".language .actions.options.switcher-options"),config);
});