/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'jquery/ui',
    'mage/translate'
], function ($, _, registry) {
    'use strict';

    $.widget('mage.miniatureGameSets', {
        /** @inheritdoc */
        _create: function () {
            this._on({
                'click': '_showPopup'
            });
        },

        /**
         * @private
         */
        _initModal: function () {
            var self = this;

            this.modal = $('<div id="create_new_attribute"></div>').modal({
                title: $.mage.__('New Sets'),
                type: 'slide',
                buttons: [],

                /** @inheritdoc */
                opened: function () {
                    $(this).parent().addClass('modal-content-new-attribute');
                    self.iframe = $('<iframe id="create_new_attribute_container"></iframe>').attr({
                        src: self._prepareUrl(),
                        frameborder: 0
                    });
                    self.modal.append(self.iframe);
                    self._changeIframeSize();
                    $(window).off().on('resize.modal', _.debounce(self._changeIframeSize.bind(self), 400));
                },

                /** @inheritdoc */
                closed: function () {
                    var doc = self.iframe.get(0).document;

                    if (doc && typeof doc.execCommand === 'function') {
                        //IE9 break script loading but not execution on iframe removing
                        doc.execCommand('stop');
                        self.iframe.remove();
                    }
                    self.modal.data('mageModal').modal.remove();
                    $(window).off('resize.modal');
                }
            });
        },

        /**
         * @return {Number}
         * @private
         */
        _getHeight: function () {
            var modal = this.modal.data('mageModal').modal,
                modalHead = modal.find('header'),
                modalHeadHeight = modalHead.outerHeight(),
                modalHeight = modal.outerHeight(),
                modalContentPadding = this.modal.parent().outerHeight() - this.modal.parent().height();

            return modalHeight - modalHeadHeight - modalContentPadding;
        },

        /**
         * @return {Number}
         * @private
         */
        _getWidth: function () {
            return this.modal.width();
        },

        /**
         * @private
         */
        _changeIframeSize: function () {
            this.modal.parent().outerHeight(this._getHeight());
            this.iframe.outerHeight(this._getHeight());
            this.iframe.outerWidth(this._getWidth());

        },

        /**
         * @return {String}
         * @private
         */
        _prepareUrl: function () {
            var productSource,
                attributeSetId = '';

            var productSource = registry.get('product_form.product_form_data_source');
            var card_game = productSource ? productSource.data.product['card_game'] : '';
            // console.log('card_game' + card_game)
            // console.log( productSource.data)

            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'card_game=' + card_game;
        },

        /**
         * @private
         */
        _showPopup: function () {
            this._initModal();
            this.modal.modal('openModal');
        }
    });

    return $.mage.miniatureGameSets;
});
