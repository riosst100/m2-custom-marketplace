define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'mage/validation',
    'text!TCGCollective_MarketPlace/template/grid/cells/publish-actions.html',
    'mage/url',
    'Magento_Ui/js/modal/modal'
], function(Column, $, mageTemplate, validation, sendmailPreviewTemplate, urlBuilder) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getFormaction: function(row) {
            return row.formaction;
        },

        getFormkey: function(row) {
            return row.formkey; // FIXED: Typo corrected from 'formkry'
        },

        getEntityid: function(row) {
            return row.entity_id;
        },
        getLabel: function(row) {
            return row[this.index + '_html'];
        },
        getWebsites: function(row) {
            return row.websites;
        },
        getTitle: function(row) {
            return row.title;
        },
        getCode: function(row) {
            return row.code;
        },
        getPublishData: function(row) {
            var publishData = row.publishData;
            return publishData ? JSON.parse(publishData) : [];
        },
        preview: function(row) {
            var _this = this;

            urlBuilder.setBaseUrl(BASE_URL);
            var url = urlBuilder.build("marketplace/catalog/product/getPublishData");
            var id = row.entity_id;

            $('body').trigger('processStart');

            $.ajax({
                url: url,
                data: {
                    form_key: window.FORM_KEY,
                    id: id,
                    current_product_website_ids: row.website_ids
                },
                showLoader: true,
                dataType: 'json',
                success: function(resp) {
                    if (resp.data) {
                        var _data = resp.data;

                        var productWebsites = _this.getWebsites(_data);

                        var modalHtml = mageTemplate(
                            sendmailPreviewTemplate, {
                                title: _this.getTitle(_data),
                                label: _this.getLabel(_data),
                                formaction: _this.getFormaction(_data),
                                formakey: _this.getFormkey(_data),
                                code: _this.getCode(_data),
                                entityid: _this.getEntityid(_data),
                                productWebsites: productWebsites,
                                publishData: _this.getPublishData(_data),
                                isCountrySelectionDisabled: productWebsites ? false : true,
                                countryLabel: $.mage.__('Country'),
                                priceLabel: $.mage.__('Price'),
                                selectlink: $.mage.__('Please select')
                            }
                        );

                        var previewPopup = $('<div id="preview-modal"></div>').html(modalHtml);
                        previewPopup.modal({
                            title: 'Publish / Edit Listing',
                            innerScroll: false,
                            clickableOverlay: true, // Aktifkan klik pada overlay
                            modalCloseBtn: true,
                            modalClass: 'publish-modal',
                            buttons: [{
                                text: $.mage.__('Close'),
                                class: 'action-close',
                                click: function () {
                                    this.closeModal();
                                }
                            }]
                        });


                        // Membuka modal
                        previewPopup.trigger('openModal');
                    }
                },
                complete: function() {
                    $('body').trigger('processStop');
                }
            });
        },
        getFieldHandler: function(row) {
            return this.preview.bind(this, row);
        }
    });
});
