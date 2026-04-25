/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'uiElement',
    'mageUtils',
    'mage/url',
    'uiRegistry'
], function ($, Element, utils, urlBuilder, uiRegistry) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            changesNotSaved: false,
            buttonLabel: 'Publish',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Has service
         *
         * @returns {Boolean} false.
         */
        hasService: function () {
            return false;
        },

        /**
         * Has addons
         *
         * @returns {Boolean} false.
         */
        hasAddons: function () {
            return false;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('changesNotSaved buttonLabel disabled visible value');

            return this;
        },
        initialize: function () {
            this._super();

            var status = this.value._latestValue;
            if (status == 1) {
                this.setUnpublishLabel();
            }

            if (status == "first_publish") {
                this.disabled(true);
            }

            return this;
        },
        setUnpublishLabel: function () {
            this.buttonLabel('Unpublish');
        },
        unpublish: function(index) {
            this.value(2);
            this.buttonLabel('Publish');

            var isPublished = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".is_published");
            if (isPublished) {
                this.changesNotSaved(true);
                isPublished.value('Draft');
            }
        },
        publishConfirm: function(index) {
            this.value(1);
            this.buttonLabel('Unpublish');

            var isPublished = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".is_published");
            if (isPublished) {
                this.changesNotSaved(true);
                isPublished.value('Published');
            }
        },
        savedChangesAndPublish: function(index) {
            var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");
            if (empty_price_error) {
                empty_price_error.visible(false);
            }

            this.publishConfirm(index);

            this.disabled(false);

            var edit_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".edit_button");
            if (edit_button) {
                edit_button.changesNotSaved(true);
                edit_button.finishEditingAction(index);
            }
        },
        publish: function (index, record) {
            var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");
            if (empty_price_error) {
                empty_price_error.visible(false);
            }
            
            var status = this.value._latestValue;
            if (status == 1) {
                this.unpublish(index);
            } else {
                 var isFirstPublish = false;

            var isFirstPublishField = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".is_first_publish");
            if (isFirstPublishField) {
                isFirstPublish = isFirstPublishField.value._latestValue
            }

            var isDefaultCountry = false;

            var isDefaultCountryField = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".is_default_country");
            if (isDefaultCountryField) {
                isDefaultCountry = isDefaultCountryField.value._latestValue
            }
            
            if (isFirstPublish || isDefaultCountry) {
                $('.admin__field-service').hide();

                var nameField = uiRegistry.get("product_form.product_form.product-details.container_name.name");
                if (nameField) {
                    nameField.disabled(false)
                }

                var shortDescriptionField = uiRegistry.get("product_form.product_form.content.container_short_description.short_description");
                if (shortDescriptionField) {
                    shortDescriptionField.disabled(false)
                }

                var descriptionField = uiRegistry.get("product_form.product_form.content.container_description.description");
                if (descriptionField) {
                    descriptionField.disabled(false)
                }

                // GET PRICE VALUE
                var price = null;
                var priceField = uiRegistry.get("product_form.product_form.product-details.container_price.price");
                if (priceField) {
                    price = priceField.value._latestValue;
                }

                // SAVE CHANGES AND PUBLISH (IF PRICE IS INPUTED)
                if (price && price != "0.00") {
                    console.log('price value :' + price);
                    console.log('called function "savedChangesAndPublish"');
                    this.savedChangesAndPublish(index);

                    return;
                }
            } else {
                $('.admin__field-service').show();

                // var priceField = uiRegistry.get("product_form.product_form.product-details.container_price.price");
                // if (priceField) {
                //     priceField.disabled(true)
                // }

                var nameField = uiRegistry.get("product_form.product_form.product-details.container_name.name");
                if (nameField) {
                    nameField.disabled(true)
                }

                var shortDescriptionField = uiRegistry.get("product_form.product_form.content.container_short_description.short_description");
                if (shortDescriptionField) {
                    shortDescriptionField.disabled(true)
                }

                var descriptionField = uiRegistry.get("product_form.product_form.content.container_description.description");
                if (descriptionField) {
                    descriptionField.disabled(true)
                }
            }
                
                
                
                
                // var storeId = this.value._latestValue;
                
                var productId = '';
    
                var editing_product_data_info = uiRegistry.get("product_form.product_form.listing-country-group.editing_product_data_info");
                if (editing_product_data_info) {
                    editing_product_data_info.visible(false);
                }
                
                var productForm = uiRegistry.get("product_form.product_form_data_source");
                if (productForm) {
                    productId = productForm.data.product.current_product_id;
                }
    
                var countryName = productForm.data.product.listing_country[index].country;
                var storeId = productForm.data.product.listing_country[index].store_id;
    
                var productDetailFieldset = uiRegistry.get("product_form.product_form.product-details");
                if (productDetailFieldset) {
                    productDetailFieldset.visible(false);
                }
    
                var contentFieldset = uiRegistry.get("product_form.product_form.content");
                if (contentFieldset) {
                    contentFieldset.visible(false);
                }

                var _this = this;

                var currentProductData = '';

                var currentProductDataField = uiRegistry.get(record.name + '.init_product_data');
                if (currentProductDataField) {
                    currentProductData = currentProductDataField.value._latestValue;
                    if (currentProductData) {
                        currentProductData = JSON.parse(currentProductData);
                    }
                }
    
                if (!currentProductData) {
                    urlBuilder.setBaseUrl(BASE_URL);
                    var url = urlBuilder.build("marketplace/catalog/product/getProductDataByCountry");
                    
                    $.ajax({
                        url: url,
                        showLoader:true,
                        data: {
                            form_key: window.FORM_KEY,
                            store_id: storeId,
                            product_id: productId
                        },
                        showLoader: true,
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.data) {
                                var productdata = uiRegistry.get(record.name + '.init_product_data');
                                if (productdata) {
                                    productdata.value(JSON.stringify(resp.data));
                                }
                                
                                if (productDetailFieldset) {
                                    productDetailFieldset.visible(true);
                                }
                                if (contentFieldset) {
                                    contentFieldset.visible(true);
                                }
                                if (editing_product_data_info) {
                                    if (countryName) {
                                        countryName = countryName.replace(' (Default Country)', '');
                                        var info = "Editing product data on <b>" + countryName + "</b> country.";
                                        editing_product_data_info.content(info);
                                        editing_product_data_info.visible(true);
                                    }
                                }
                                var data = resp.data;
                                if (data) {
                                    var priceExist = false;
                                    _.each(data, function (value, code) {
                                        var field = uiRegistry.get("product_form.product_form.product-details.container_" + code + "." + code);
                                        if (field) {
                                            if (code == "price") {
                                                if (!value || value == undefined || value == 0) {
                                                    var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");
                                                    if (empty_price_error) {
                                                        empty_price_error.visible(true);

                                                        const offset = 100;

                                                        $('html, body').animate({
                                                            scrollTop: $('.empty-price-error-mg').offset().top - offset
                                                        }, 500);
                                                    }
                                                    value = '';
                                                } else {
                                                    _this.publishConfirm(index);

                                                    var edit_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".edit_button");
                                                    if (edit_button) {
                                                        edit_button.changesNotSaved(true);
                                                    }

                                                    value = parseFloat(value).toFixed(2);

                                                    priceExist = true;
                                                }
                                            }
                                            
                                            field.value(value);
                                        }
                                    });
                                }
    
                                if (!priceExist) {
                                    var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");
                                    if (empty_price_error) {
                                        empty_price_error.visible(true);
                                    }

                                    const offset = 100;

                                    $('html, body').animate({
                                        scrollTop: $('.empty-price-error-mg').offset().top - offset
                                    }, 500);
                                    
                                    // _this.disabled(true);
                                    
                                    if (productDetailFieldset) {
                                        productDetailFieldset.visible(true);
                                    }
                                    if (contentFieldset) {
                                        contentFieldset.visible(true);
                                    }
                                    if (editing_product_data_info) {
                                        if (countryName) {
                                            countryName = countryName.replace(' (Default Country)', '');
                                            var info = "Editing listing data on <b>" + countryName + "</b> country.";
                                            editing_product_data_info.content(info);
                                            editing_product_data_info.visible(true);
                                        }
                                    }

                                    var listing_country = uiRegistry.get("product_form.product_form.listing-country-group.listing_country");
                                    if (listing_country) {
                                        var count = listing_country._elems.length;
                                        if (count) {
                                            for (let i = 0; i < count; i++) {
                                                // if (i != this.index) {
                                                // product_form.product_form.listing-country-group.listing_country.1.edit_button
                                                var edit_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+i+".edit_button");
                                                if (edit_button) {
                                                    if (edit_button.name != "product_form.product_form.listing-country-group.listing_country."+index+".edit_button") {
                                                        // edit_button.editingFlag(false);
                                                    } else {
                                                        // edit_button.editingFlag(true);
                                                        edit_button.changesNotSaved(false);
                                                    }
                                                }

                                                var publish_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+i+".publish_button");
                                                if (publish_button) {
                                                    if (publish_button.name != "product_form.product_form.listing-country-group.listing_country."+index+".publish_button") {
                                                        if (!isFirstPublish) {
                                                            // publish_button.disabled(false);
                                                        }
                                                    } else {
                                                        // publish_button.disabled(true);
                                                    }
                                                }
                        
                                            }
                        
                                        }
                                    }
                                }
                            }
                        },
                        complete: function() {
                            $('body').trigger('processStop');
                        }
                    });
                } else {
                    
                    // Data already fetched or edit
                    var currentProductDataField = uiRegistry.get(record.name + '.product_data');
                    if (currentProductDataField) {
                        currentProductData = currentProductDataField.value._latestValue;
                        if (currentProductData) {
                            currentProductData = JSON.parse(currentProductData);
                        }
                    }
                    

                    var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");

                    if (!currentProductData.price) {
                        if (empty_price_error) {
                            empty_price_error.visible(true);
                        }

                        const offset = 100;

                        $('html, body').animate({
                            scrollTop: $('.empty-price-error-mg').offset().top - offset
                        }, 500);
                        
                        // _this.disabled(true);
                        if (productDetailFieldset) {
                            productDetailFieldset.visible(true);
                        }
                        if (contentFieldset) {
                            contentFieldset.visible(true);
                        }
                        if (editing_product_data_info) {
                            if (countryName) {
                                countryName = countryName.replace(' (Default Country)', '');
                                var info = "Editing product data on <b>" + countryName + "</b> country.";
                                editing_product_data_info.content(info);
                                editing_product_data_info.visible(true);
                            }
                        }

                        var listing_country = uiRegistry.get("product_form.product_form.listing-country-group.listing_country");
                        if (listing_country) {
                            var count = listing_country._elems.length;
                            if (count) {
                                for (let i = 0; i < count; i++) {
                                    // if (i != this.index) {
                                    // product_form.product_form.listing-country-group.listing_country.1.edit_button
                                    var edit_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+i+".edit_button");
                                    if (edit_button) {
                                        if (edit_button.name != "product_form.product_form.listing-country-group.listing_country."+index+".edit_button") {
                                            // edit_button.editingFlag(false);
                                        } else {
                                            // edit_button.editingFlag(true);
                                            edit_button.changesNotSaved(false);
                                        }

                                        var publish_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+i+".publish_button");
                                                if (publish_button) {
                                                    if (publish_button.name != "product_form.product_form.listing-country-group.listing_country."+index+".publish_button") {
                                                        // publish_button.disabled(false);
                                                    } else {
                                                        // publish_button.disabled(true);
                                                    }
                                                }
                                    }
            
                                }
            
                            }
                        }
                    } else {
                        if (empty_price_error) {
                            empty_price_error.visible(false);
                        }

                        _this.publishConfirm(index);

                        _this.disabled(false);

                        var edit_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+index+".edit_button");
                        if (edit_button) {
                            edit_button.changesNotSaved(true);
                        }
                    }
                    
                }
            }
        }
    });
});