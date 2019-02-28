var config = {
    map: {
        "*": {
            magebeesQuoteToCart: 'Magebees_QuotationManagerPro/js/catalog-add-to-cart',	quoteSidebar:'Magebees_QuotationManagerPro/js/sidebar',
magebeesQuote:'Magebees_QuotationManagerPro/js/quotation',
			'Magento_ConfigurableProduct/js/options-updater':'Magebees_QuotationManagerPro/js/options-updater',
			 SwatchRenderer: 'Magebees_QuotationManagerPro/js/swatch-renderer'
           
        }
    },
	 config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Magebees_QuotationManagerPro/js/swatch-renderer': true
            }
        }
    }
};

