define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
			_create: function () {				
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                productData = this._determineProductData(),
                $main = productData.isInProductView ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (productData.isInProductView) {
                gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }

            this.productForm = this.element.parents(this.options.selectorProductTile).find('form');
            this.inProductList = this.productForm.length > 0;
        }
           
        });

        return $.mage.SwatchRenderer;
    }
});
