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
            return row.formkry;
        },

        getEntityid: function(row) {
            return row.entity_id;
        },
        getLabel: function(row) {
            return row[this.index + '_html']
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
            var _data = null;
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
                        _data = resp.data;
                        
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
                        var previewPopup = $('<div></div>').html(modalHtml);
                        previewPopup.modal({
                            title: $.mage.__('Select Country To Publish'),
                            innerScroll: false,
                            clickableOverlay: true,
                            modalCloseBtn: '',
                            modalClass: 'publish-modal',
                            buttons: []
                        }).trigger('openModal');
                        
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