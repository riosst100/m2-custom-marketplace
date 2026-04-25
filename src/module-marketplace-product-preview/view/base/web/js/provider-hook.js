define(['jquery', 'uiRegistry', 'mage/url', 'Magento_Ui/js/modal/modal'], function($, uiRegistry, urlBuilder, modal) {
    'use strict';

    return function(Provider) {
        return Provider.extend({
            defaults: {
                clientConfig: {
                    urls: {
                        save: '${ $.submit_url }',
                        beforeSave: '${ $.validate_url }'
                    }
                },
                ignoreTmpls: {
                    data: true
                }
            },
            initialize: function () {
                this._super()
                    .initClient();

                var _this = this;

                console.log(this.index)
                
                if (!this.index.includes('pagebuilder') && (this.index.includes('_singles') || this.index.includes('_sets') || this.index.includes('_sealedproducts'))) {
                    $.ajax({
                        url: '/backoffice/productattributeslink/platformDatabase/getAllowedFieldsFromAttributeSet',
                        data: {
                            form_key: window.FORM_KEY
                        },
                        showLoader: false,
                        dataType: 'json',
                        success: function(resp) {
                            if (resp) {
                                const hidePlatformDbEditFields = setInterval(() => {
                                    var fields = $('fieldset[class="admin__fieldset"]').find('input, select');
                                    if (fields.length) {
                                        clearInterval(hidePlatformDbEditFields);
                                        fields.each(function () {
                                            var $el = $(this);
                                            var name = $el.attr('name');
            
                                            if (resp.data.indexOf(name) === -1) {
                                                $('div[data-index="' + name + '"]').hide();
                                            }
            
                                        });
            
                                        console.log('Allowed Fields ',resp)
                                    }
                                }, 500);
                            }
                        }
                    });
                    
                }

                const intervalId = setInterval(() => {
                    var gallery = uiRegistry.get('product_form.product_form.gallery');
                    if (gallery) {
                        clearInterval(intervalId); // Stop the interval
                        
                        var visible = false;
                        var productSource = uiRegistry.get('product_form.product_form_data_source');
                        if (productSource) {
                            var entityId = productSource.data.product['current_product_id'] ?? null;
                            if (entityId) {
                                visible = true;
                            }
                        }
        
                        // Get the current URL
                        const url = window.location.href;
                        
                        // Extract the value of the "set" parameter
                        const paramValue = url.split('/source/')[1]?.split('/')[0];
                        const productType = url.split('/type/')[1]?.split('/')[0];
                        const action = url.split('/action/')[1]?.split('/')[0];
                        
                        // if (paramValue != "database" && productType != "configurable") {
                        //     visible = true;
                        // }

                        if (action == "publish") {
                            visible = false;
                            
                        }

                        gallery.visible(visible)
                    }
                }, 500);

                const intervalId2 = setInterval(() => {
                    var quantity_and_stock_status = $('div[data-index="quantity_and_stock_status"]');
                    if (quantity_and_stock_status.length) {
                        clearInterval(intervalId2);
                        
                        // Get the current URL
                        const url = window.location.href;
                        
                        // Extract the value of the "set" parameter
                        const action = url.split('/action/')[1]?.split('/')[0];
                        if (action == "publish") {
                            
                            // quantity_and_stock_status.hide()
                            
                            
                        }
                    }
                }, 200);
            },
            /**
             * Saves currently available data.
             *
             * @param {Object} [options] - Addtitional request options.
             * @returns {Provider} Chainable.
             */
            save: function (options) {
                var data = this.get('data');

                if (data.is_preview) {
                    options.ajaxSave = true;
                }

                var response = this.client.save(data, options);

                if (response && data.is_preview) {
                    this.previewProduct(data, options);
                }

                return this;
            },
            previewProduct: function (data, options) {
                console.log('NEW start preview')
                console.log(data)
    
                var client = this.client;
    
                $.ajax({
                    url: '/marketplace/catalog/product/preview',
                    data: {
                        form_key: window.FORM_KEY,
                        data: data
                    },
                    showLoader: true,
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data.url) {
                            window.open(resp.data.url, '_blank');
                        }
                        
                        window.location.reload();
                    }
                });
    
                return true;
            }
        });
    }
});
