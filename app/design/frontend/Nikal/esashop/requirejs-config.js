var config = {
    deps: [
        "js/logo",
        "js/switchers-zindex-fix"
    ],

    map: {
        "*": {
        }
    },

    paths: {
    },

    shim: {
    },

    config: {
        mixins: {
            'Magento_Ui/js/modal/modal': {
                'Magento_Ui/js/model/modal-mixin': true
            }
        }
    }
};