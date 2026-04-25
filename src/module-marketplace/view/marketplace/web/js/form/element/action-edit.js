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
            buttonLabel: 'Edit Listing Data',
            changesNotSaved: false,
            editingFlag: false,
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
                .observe('buttonLabel disabled visible value changesNotSaved editingFlag');

            return this;
        },
        initialize: function () {
            this._super();

            var status = this.value._latestValue;
            if (status == "editing") {
                this.setEditing();
            }

            if (status == "first_publish") {
                this.disabled(true);
            }

            return this;
        },
        setEditing: function () {
            // this.disabled(true);
            this.editingFlag(true);
            this.changesNotSaved(false);
            this.buttonLabel('Finish Editing');
        },
        finishEditingAction: function(index, record = null) {
            this.editingFlag(false)

            var publishBtnField = uiRegistry.get("product_form.product_form.listing-country-group.listing_country." + index + ".publish_button");
            if (publishBtnField) {
                publishBtnField.disabled(false);
            }

            // var priceField = uiRegistry.get("product_form.product_form.product-details.container_price.price");
            // if (priceField) {
            //     priceField.disabled(true)
            // }

            var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");
            if (empty_price_error) {
                empty_price_error.visible(false);
            }

            var productDetailFieldset = uiRegistry.get("product_form.product_form.product-details");
            if (productDetailFieldset) {
                productDetailFieldset.visible(false);
            }

            var contentFieldset = uiRegistry.get("product_form.product_form.content");
            if (contentFieldset) {
                contentFieldset.visible(false);
            }

            var editing_product_data_info = uiRegistry.get("product_form.product_form.listing-country-group.editing_product_data_info");
            if (editing_product_data_info) {
                editing_product_data_info.visible(false);
            }

            // Compare changes on product data
            var havingChanges = this.compareChanges(index);
            if (havingChanges) {
                this.changesNotSaved(true);
            }

            const offset = 100;

            $('html, body').animate({
                scrollTop: $('.need-save-reminder').offset().top - offset
            }, 500);
        },
        compareChanges: function(index) {
            var currentPoductDataField = uiRegistry.get("product_form.product_form.listing-country-group.listing_country." + index + ".init_product_data");
            if (currentPoductDataField) {
                var currentPoductData = currentPoductDataField.value._latestValue;
                if (currentPoductData) {
                    var changedProductData = null;
                    
                    var productSource = uiRegistry.get("product_form.product_form_data_source");
                    if (productSource) {
                        changedProductData = productSource.data.product;
                    }

                    if (changedProductData) {
                        currentPoductData = JSON.parse(currentPoductData);

                        console.log('init data')
                        console.log(currentPoductData)

                        console.log('changedProductData')
                        console.log(changedProductData)
        
                        const result = this.compareData(currentPoductData, changedProductData);

                        console.log('result', result)

                        if (Object.keys(result).length > 0) {
                            var changesPoductDataField = uiRegistry.get("product_form.product_form.listing-country-group.listing_country." + index + ".product_data");
                            changesPoductDataField.value(JSON.stringify(changedProductData, null, 2));
                            
                            return true;
                        }
                    }
                }
            }
            
            return false;  
        },
        normalizeValue: function(value) {
            if (value === "") return null;
            if (value === "1") return true;
            if (value === "0") return false;
            return value;
        },
      compareData: function(data1, data2) {
    const differences = {};
    const fields = ["name", "price"]; // Fields to compare

    // Find common keys between data1 and data2 that are in the fields array
    const commonKeys = Object.keys(data1).filter(
        (key) => fields.includes(key)
    );

    // Compare values for common keys
    commonKeys.forEach(key => {
        let val1 = this.normalizeValue(data1[key]);
        let val2 = this.normalizeValue(data2[key]);

        // Check for nested objects
        if (typeof val1 === "object" && typeof val2 === "object" && val1 !== null && val2 !== null) {
            const nestedDiff = this.compareData(val1, val2);
            if (Object.keys(nestedDiff).length > 0) {
                differences[key] = nestedDiff;
            }
        } else {
            // Special handling for 'price' field: compare as floats
            if (key === "price") {
                val1 = val1 !== undefined ? parseFloat(val1) : undefined; // Parse as float if defined
                val2 = val2 === '0.00' || val2 === false ? 0 : parseFloat(val2); // Normalize '0.00' and false to 0

                // Treat NaN as equivalent to 0
                if (isNaN(val1)) val1 = 0;
                if (isNaN(val2)) val2 = 0;

                // Skip if both values are equivalent to 0
                if (val1 === 0 && val2 === 0) {
                    return;
                }
            }

            // Check if values are different
            if (val1 !== val2) {
                differences[key] = { data1: val1, data2: val2 };
            }
        }
    });

    // Find keys in data2 that are not in data1 and add them to differences
    Object.keys(data2).forEach(key => {
        if (!data1.hasOwnProperty(key) && fields.includes(key)) {
            const val2 = this.normalizeValue(data2[key]);

            // Special case for 'price': skip if data1 is undefined, NaN, and data2 is 0, '0.00', or false
            if (key === "price" && ((data1[key] === undefined || isNaN(data1[key])) && (val2 === 0 || val2 === '0.00' || val2 === false || isNaN(val2)))) {
                return;
            }

            differences[key] = { data1: undefined, data2: val2 };
        }
    });

    return differences;
},





        editProductData: function (index, record) {
            // var priceField = uiRegistry.get("product_form.product_form.product-details.container_price.price");
            //     if (priceField) {
            //         priceField.disabled(false)
            //     }
            
            var empty_price_error = uiRegistry.get("product_form.product_form.listing-country-group.empty_price_error");
            if (empty_price_error) {
                empty_price_error.visible(false);
            }

            

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
            } else {
                $('.admin__field-service').show();

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
            
            this.setEditing();

            var listing_country = uiRegistry.get("product_form.product_form.listing-country-group.listing_country");
            if (listing_country) {
                var count = listing_country._elems.length;
                if (count) {
                    for (let i = 0; i < count; i++) {
                        // if (i != this.index) {
                        var edit_button = uiRegistry.get("product_form.product_form.listing-country-group.listing_country."+i+".edit_button");
                        if (edit_button && edit_button.name != this.name) {
                            // edit_button.value('pending')
                            // edit_button.disabled(false);
                            edit_button.editingFlag(false);
                        // }
                        }

                    }

                }
            }
            
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

            var currentProductData = '';

            var currentProductDataField = uiRegistry.get(record.name + '.init_product_data');
            if (currentProductDataField) {
                currentProductData = currentProductDataField.value._latestValue;
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
                            if (currentProductDataField) {
                                currentProductDataField.value(JSON.stringify(resp.data));
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
                                    var info = "Editing listing data on <b>" + countryName + "</b> country.";
                                    editing_product_data_info.content(info);
                                    editing_product_data_info.visible(true);
                                }
                            }
                            var data = resp.data;
                            if (data) {
                                const offset = 100;

                                $('html, body').animate({
                                    scrollTop: $('[data-index="listing_country"]').offset().top - offset
                                }, 500);
                                // if (!data.price || data.price == undefined) {
                                //     data.price = parseFloat('0.00');
                                // }
                                _.each(data, function (value, code) {
                                    var field = uiRegistry.get("product_form.product_form.product-details.container_" + code + "." + code);
                                    if (field) {
                                        if (code == "price") {
                                            if (!value || value == undefined || value == 0) {
                                                value = '';
                                            } else {
                                                value = parseFloat(value).toFixed(2);
                                            }
                                        }
                                        
                                        // if (!options.some(item => item.value === field.value._latestValue)) {
                                            field.value(value);
                                        // }
                                        
                                        // field.isDisplayMissingValuePlaceholder = false;
                                        // if (field.multiple) {
                                        //     field.value(['']);
                                        // }
    
                                        // field.renderPath = false;
    
                                        // field.options(options);
                                        // field.cacheOptions.plain = options;
                                        // field.cacheOptions.tree = options;
                                        // field.visible(true);
                                    }
                                });
                            }
                        }
                    },
                    complete: function() {
                        $('body').trigger('processStop');
                    }
                });
            } else {
                if (editing_product_data_info) {
                    if (countryName) {
                        countryName = countryName.replace(' (Default Country)', '');
                        var info = "Editing product data on <b>" + countryName + "</b> country.";
                        editing_product_data_info.content(info);
                        editing_product_data_info.visible(true);
                    }
                }
                
                const offset = 100;

                        $('html, body').animate({
                            scrollTop: $('[data-index="listing_country"]').offset().top - offset
                        }, 500);
                if (productDetailFieldset) {
                    productDetailFieldset.visible(true);
                }
                if (contentFieldset) {
                    contentFieldset.visible(true);
                }
                
            }
        }
    });
});