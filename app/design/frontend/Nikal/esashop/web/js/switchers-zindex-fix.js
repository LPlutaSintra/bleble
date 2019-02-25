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
    if(document.querySelector(".currency .actions.options.switcher-options")) {
        mut.observe(document.querySelector(".currency .actions.options.switcher-options"),config);
    }
    if(document.querySelector(".language .actions.options.switcher-options")) {
        mut.observe(document.querySelector(".language .actions.options.switcher-options"),config);
    }
    if(document.querySelector(".shipping .actions.options.switcher-options")) {
        mut.observe(document.querySelector(".shipping .actions.options.switcher-options"),config);
    }

});