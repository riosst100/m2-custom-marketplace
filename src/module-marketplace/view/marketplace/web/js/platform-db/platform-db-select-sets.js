/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'platformDatabaseUtils',
    'mage/translate'
], function (Component, $, _, registry, platformDatabaseUtils) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-cardgame-sets-matrix]').on('add', function () {
            provider().reload();
        });
    };

    return Component.extend({
        attributesLabels: {},
        stepInitialized: false,
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }',
                setsAndSinglesComponent: '${ $.setsAndSinglesComponent }',
                modalComponent: '${ $.modalComponent }',
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedSets: [],
            sets: [],
            categoryMetaData: null,
            isSingles: null,
            isSealedProducts: null,
            mainSetsCode: null,
            mainEntityCode: null
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        
        initObservable: function () {
            this._super().observe(['selectedSets','sets','categoryMetaData','isSealedProducts','isSingles','mainSetsCode','mainEntityCode']);

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();

            var categoryMetaData = platformDatabaseUtils.getCategoryMetaData();
            if (categoryMetaData) {
                this.categoryMetaData = categoryMetaData;
                this.isSealedProducts = categoryMetaData.isSealedProducts;
                this.isSingles = categoryMetaData.isSingles;
                this.mainSetsCode = categoryMetaData.mainSetsCode;
                this.mainEntityCode = categoryMetaData.mainEntityCode;
            }

            platformDatabaseUtils.hideSteps(wizard);
            platformDatabaseUtils.modifyFilterElements(wizard);
            platformDatabaseUtils.filterDataProvider(this, wizard);
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('Please select sets.');

            /*eslint-enable max-len*/

            // if (this.mode === 'edit') {
                // this.wizard.setNotificationMessage(msg);
            // }
        },

        /**
         * Do select saved attributes.
         */
        doSelectSavedAttributes: function () {
            if (this.stepInitialized === false) {
                this.stepInitialized = true;
                //cache attributes labels, which can be present on the 2nd page
                _.each(this.initData.attributes, function (attribute) {
                    this.attributesLabels[attribute.id] = attribute.label;
                }.bind(this));
                this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            }
        },

        /**
         * @param {*} selected
         */
        doSelectedAttributesLabels: function (selected) {
            var labels = [];

            var multiselect = this.multiselect();

            if (this.isSingles || this.isSealedProducts) {
                if (selected.length) {
                    selected.forEach(function(data, index) {
                        if (index != selected.length-1) {
                            multiselect.toggleSelect(data);
                            selected.splice(index, 1);
                        }
                    });
                }
            }

            this.sets([])
            
            this.selected = selected;

            const mainSetsCode = this.categoryMetaData ? this.categoryMetaData.mainSetsCode : null;
            
            _.each(selected, function (entityId) {
                var set;

                set = _.findWhere(this.multiselect().rows(), {
                    'entity_id': entityId
                });

                if (set) {
                    if (this.isSingles || this.isSealedProducts) {
                        this.sets(set);
                    } else {
                        this.sets.push(set);
                    }

                    this.attributesLabels[set['entity_id']] = set[mainSetsCode] ?? 'Untitled';
                }
                
                labels.push(this.attributesLabels[entityId]);
            }.bind(this));

            // Update selected sets label
            this.selectedSets(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.sets = this.sets();

            if (!this.selectedSets()) {
                throw new Error($.mage.__('Please select sets.'));
            }
            
            var setsData = this.sets();

            var _this = this;

            var setsMatrixField = registry.get('product_form.product_form.cardgame_sets.sets-matrix');
            
            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            var productSource = registry.get('product_form.product_form_data_source');
            var is_sealedproducts = productSource ? productSource.data.product['is_sealedproducts'] : '';
            
            wizard.data.sets = this.sets();
            this.setNotificationMessage();

            if (!this.isSingles && !this.isSealedProducts) {
                wizard.data.setId = this.multiselect().selected;
                
                var setsData = this.sets()[0];
                var setsDataArr = this.sets();
    
                var setsMatrixField = registry.get('product_form.product_form.cardgame_sets.sets-matrix');
                
                if (wizard.data.setId() && wizard.data.setId().length != 0) 
                {
                    wizard.data.sets = setsData;
    
                    var imagesArr = [];
                    
                    setsDataArr.forEach(function(data, index) {
                        
                        var allImages = data.all_images;
                        if (allImages) {
                            if (!allImages.includes('placeholder.jpg')) {
                                var str_array = allImages.split(',');
                                
                                for(var i = 0; i < str_array.length; i++) {
                                    str_array[i] = str_array[i].replace(/^\s*/, "").replace(/\s*$/, "");
                                    if (platformDatabaseUtils.checkURL(str_array[i])) {
                                        if (i == 0) {
                                            $('body').trigger('processStart');
                                        }
                                        imagesArr.push(str_array[i]);
                                    }
                                }
                            }
                        }
                    })

                    try {
                        if (imagesArr.length) {
                            platformDatabaseUtils.uploadImagesBatch(imagesArr);
                        }
                    } catch (e) {
                        console.error('Upload batch error', e);
                    }
    
                    var productSource = registry.get('product_form.product_form_data_source');
                    
                    var sellerId = productSource.data.product['seller_id'] ?? '';
                    var setName = setsData[this.mainSetsCode];
    
                    var mainEntityValue = productSource.data.product[this.mainEntityCode] ?? '';
                    var mainEntityField = registry.get(
                        'product_form.product_form.category-details.container_' + this.mainEntityCode + '.' + this.mainEntityCode
                    );
                    
                    var options = mainEntityField.options._latestValue;
                    var mainEntityLabel = platformDatabaseUtils.getLabelByValue(options, mainEntityValue);
                    
                    var setAbbrv = null;
                    var productNameArr = [];
                    var productIdentifierArr = [];
    
                    if (setName) {
                        if (setAbbrv) {
                            productIdentifierArr.push(
                                this.convertToSlug(setAbbrv).toUpperCase()
                            );
                            setName = setName + ' (' + setAbbrv + ')';
                        }
                        
                        productNameArr.push(setName);
                    }
    
                    if (mainEntityLabel) {
                        productNameArr.push(mainEntityLabel);
                    }
    
                    productIdentifierArr.push(
                        platformDatabaseUtils.makeid(10).toUpperCase()
                    );
                    
                    var productName = productNameArr.join(' | ');
                    var productIdentifier = productIdentifierArr.join('-');
    
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
                    urlKeyField.value(platformDatabaseUtils.convertToSlug(setName) + '-' + productIdentifier.toLowerCase());
                    
                    setsMatrixField.visible(true);
                } else {
                    setsMatrixField.visible(false);
                }
    
                setsMatrixField.visible(true);
                
                this.setsAndSinglesComponent().render(this.sets());
                this.modalComponent().closeModal();
            }
        },
        back: function () {}
    });
});
