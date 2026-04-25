define([
    'jquery',
    'underscore',
    'mage/template',
    'uiRegistry',
    'productGallery',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget',
    'baseImage'
], function ($, _, mageTemplate, registry, productGallery) {
    'use strict';
    $.widget('mage.productGallery', $.mage.productGallery, {
        options: {
            dialogTemplate: '[data-role=img-dialog-tmpl]',
            dialogContainerTmpl: '[data-role=img-dialog-container-tmpl]'
        },

        _create: function () {
            var template = this.element.find(this.options.dialogTemplate),
                containerTmpl = this.element.find(this.options.dialogContainerTmpl);

            this._super();
            this.modalPopupInit = false;

            if (template.length) {
                this.dialogTmpl = mageTemplate(template.html().trim());
            }

            if (containerTmpl.length) {
                this.dialogContainerTmpl = mageTemplate(containerTmpl.html().trim());
            } else {
                this.dialogContainerTmpl = mageTemplate('');
            }

            this._initDialog();
        },
        
        _showDialog: function (imageData) {
            var $imageContainer = this.findElement(imageData),
                $template;

            console.log(imageData)

            $template = this.dialogTmpl({
                'data': imageData
            });

            this.$dialog
                .html($template)
                .data('imageData', imageData)
                .data('imageContainer', $imageContainer)
                .modal('openModal');
        },
    });
    return $.mage.productGallery;
});