/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'uiRegistry',
    'jquery',
    'underscore',
    'mage/translate',
    'platformDatabaseUtils'
], function (Component, registry, $, _, $t, platformDatabaseUtils) {
    'use strict';

    return Component.extend({
        sealedProductsLabels: {},
        stepInitialized: false,
        defaults: {
            modules: {
                setsAndSinglesComponent: '${ $.setsAndSinglesComponent }',
                modalComponent: '${ $.modalComponent }',
                multiselect: '${ $.multiselectName }',
                sealedProductsProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedSealedProductsLabels'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedSealedProducts: [],
            attributes: [],
            sealedProducts: [],
            release: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedSealedProducts','attributes', 'sealedProducts','release']);

            return this;
        },
        variations: [],
        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.release(wizard.data.release);
            this.setNotificationMessage();

            var sealedProductsGrid = this.sealedProductsProvider();

            var release = wizard.data.release;

            sealedProductsGrid.set('params.sc_release', release.sc_release);
            sealedProductsGrid.reload();

            var mainEntityCode = 'sc_categories';

            var productSource = registry.get('product_form.product_form_data_source');
            var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
            var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';

            var listingGridProvider = this.sealedProductsProvider();
            if (listingGridProvider) {
                let currentParams = listingGridProvider.get('params') || {};

                let newParams = Object.assign({}, currentParams, {
                    [mainEntityCode]: mainEntityValue,
                    is_singles: isHavingSingles ? 1 : 0
                });

                newParams.grid_filters = {};
                
                var release = wizard.data.release;
                if (release) {
                    newParams.grid_filters['sc_release'] = release.sc_release;
                }

                listingGridProvider.set('params', newParams);

                platformDatabaseUtils.updateFilters(newParams, wizard);
            }
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('Please select sealed products.');
        },

        /**
         * @param {*} selected
         */
        doSelectedSealedProductsLabels: function (selected) {
            var labels = [];

            var multiselect = this.multiselect()

            /* COMMENTED TO ENABLE MULTISELECT */
            // if (selected.length) {
            //     selected.forEach(function(data, index) {
            //         if (index != selected.length-1) {
            //             multiselect.toggleSelect(data);
            //             selected.splice(index, 1);
            //         }
            //     });
            // }
            
            this.selected = selected;
            this.sealedProducts = [];
            _.each(selected, function (entityId) {
                var sealedProducts;

                // if (!this.sealedProductsLabels[entityId]) {
                    sealedProducts = _.findWhere(this.multiselect().rows(), {
                        'entity_id': entityId
                    }); 

                    if (sealedProducts) {
                        this.sealedProducts.push(sealedProducts);
                        this.sealedProductsLabels[sealedProducts['entity_id']] = sealedProducts['sc_sealed_products'];
                    }
                // }
                labels.push(this.sealedProductsLabels[entityId]);
            }.bind(this));
            this.selectedSealedProducts(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.sealedProductsId = this.multiselect().selected;
            
            if (!wizard.data.sealedProductsId() || wizard.data.sealedProductsId().length === 0) {
                throw new Error($.mage.__('Please select Sealed Products.'));
            }

            var sealedProductsData = this.sealedProducts[0];
            var setsData = this.release();
            var releaseData = wizard.data.release;
            
            var setsMatrixField = registry.get('product_form.product_form.sportcard_sets.sets-matrix');
            var sealedProductsMatrixField = registry.get('product_form.product_form.sportcard_sets.sealedproducts-matrix');

            if (wizard.data.sealedProductsId() && wizard.data.sealedProductsId().length != 0) 
            {
                wizard.data.sealedProducts = sealedProductsData;

                var allImages = sealedProductsData.all_images;
                if (allImages) {
                    if (!allImages.includes('placeholder.jpg')) {
                        var str_array = allImages.split(',');
                        
                        for(var i = 0; i < str_array.length; i++) {
                            str_array[i] = str_array[i].replace(/^\s*/, "").replace(/\s*$/, "");
                            if (this.checkURL(str_array[i])) {
                                this.uploadImage(str_array[i]);
                            }
                        }
                    }
                }

                

                sealedProductsMatrixField.visible(true);
            } else {
                sealedProductsMatrixField.visible(false);
            }

            var productSource = registry.get('product_form.product_form_data_source');
                
                var sellerId = productSource.data.product['seller_id'] ?? '';
                            
                var attrCode = 'sc_categories';
                var cardName = sealedProductsData ? sealedProductsData.sc_sealed_products : null;
                var cardNumber = sealedProductsData ? sealedProductsData.card_number : null;
                // var setName = setsData.card_set;
                // var setAbbrv = setsData.card_set_abbreviation;

                var mainEntityValue = productSource.data.product[attrCode] ?? '';
                var mainEntityField = registry.get(
                    'product_form.product_form.category-details.container_' + attrCode + '.' + attrCode
                );
                
                var options = mainEntityField.options._latestValue;
                var mainEntityLabel = this.getLabelByValue(options, mainEntityValue);

                var productNameArr = [];
                var productIdentifierArr = [];
                var urlKeyArr = [];

                if (cardName) {
                    if (cardNumber) {
                        productIdentifierArr.push(
                            this.convertToSlug(cardNumber).toUpperCase()
                        );
                        cardName = cardName + ' ' + '#' + cardNumber;
                    }

                    urlKeyArr.push(this.convertToSlug(cardName));
                    productNameArr.push(cardName);
                }

                // if (setName) {
                //     if (setAbbrv) {
                //         productIdentifierArr.push(
                //             this.convertToSlug(setAbbrv).toUpperCase()
                //         );
                //         setName = setName + ' (' + setAbbrv + ')';
                //     }
                    
                //     productNameArr.push(setName);
                // }

                if (releaseData) {
                    productNameArr.push(releaseData.sc_release);
                }

                if (mainEntityLabel) {
                    productNameArr.push(mainEntityLabel);
                }

                productIdentifierArr.push(
                    this.makeid(10).toUpperCase()
                );
                
                var productName = productNameArr.join(' | ');
                var productIdentifier = productIdentifierArr.join('-');
                urlKeyArr.push(productIdentifier.toLowerCase());
                var urlKey = urlKeyArr.join('-');

                // Set product name
                var productNameField = registry.get('product_form.product_form.product-details.container_name.name');
                productNameField.value(productName);

                // Set sku
                var sellerSkuField = registry.get('product_form.product_form.product-details.container_supplier_sku.supplier_sku');
                sellerSkuField.value(productIdentifier);

                var skuField = registry.get('product_form.product_form.product-details.container_sku.sku');
                skuField.value(productIdentifier);

                // Set url key
                var urlKeyField = registry.get('product_form.product_form.search-engine-optimization.container_url_key.url_key');
                urlKeyField.value(urlKey);
            
            this.setsAndSinglesComponent().render(null, null, this.sealedProducts, this.release());
            this.modalComponent().closeModal();
        },
        checkURL: function(url) {
            return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
        },
        makeid: function(length) {
            let result = '';
            const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
              result += characters.charAt(Math.floor(Math.random() * charactersLength));
              counter += 1;
            }
            return result;
        },
        getLabelByValue: function(data, value) {
            const item = data.find(entry => entry.value === value);
            return item ? item.label : null;
        },
        convertToSlug: function(Text) {
          return Text.toLowerCase()
            .replace(/ /g, "-")
            .replace(/[^\w-]+/g, "");
        },
        uploadImage: async function(imgUrl) {
            imgUrl = imgUrl.replace("/.thumbs","");
            
            let response = await fetch(imgUrl);
              let data = await response.blob();
              let metadata = {
                type: 'image/jpeg'
              };
                var filename = imgUrl.match(/.*\/(.*)$/)[1];
              let file = new File([data], filename, metadata);
            $(document).find('input[type=file]').fileupload('send', {files: [file]});

            return true;
        },

        /**
         * Back.
         */
        back: function () {
        }
    });
});
