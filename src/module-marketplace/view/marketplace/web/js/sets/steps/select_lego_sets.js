/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/translate'
], function (Component, $, _, registry) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-legosets-sets-matrix]').on('add', function () {
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
            sets: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        
        initObservable: function () {
            this._super().observe(['selectedSets','sets']);

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();

            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            // if (!is_having_singles) {
            var step2link = $('a[href="#select-legosets-sets-steps-wizard_step2"');
            if (step2link) {
                var step2 = step2link.parent();
                if (step2) {
                    if (!is_having_singles) {
                        step2.hide();
                    } else {
                        step2.show();
                    }
                }
            }
            
            var stepsWizard = registry.get('product_form.product_form.legosets.setsModal.wizard');
                if (stepsWizard) {
                    // stepsWizard.visible = false
                    // var steps = stepsWizard.steps;
                    // var stepsNames = stepsWizard.stepsNames;
                    // var _elems = stepsWizard._elems;
                    
                    // var elems = stepsWizard.elems();

                    // steps.splice(1, 1);
                    // stepsNames.splice(1, 1);
                    // elems.splice(1, 1);

                    // var step2 = elems[1];

                    // step2.disabled(true);

                    // stepsWizard.steps = steps;
                    // stepsWizard.stepsNames = stepsNames;
                    // stepsWizard._elems = elems;
                    // stepsWizard.elems = elems;
                }

            
            $(document).ready(function(){
                var test = setInterval(function() {
                    var filterWrap = $('.admin__data-grid-filters-wrap');
                    if (filterWrap.length) {
                        clearInterval(test);
                        filterWrap.css('padding','15px 0');
                        filterWrap.find('button[data-action="grid-filter-cancel"]').hide();
                        filterWrap.find('fieldset').css('padding','0px');
                        $('.admin__data-grid-filters-wrap').addClass('_show');
                    }
                }, 50);

                var test3 = setInterval(function() {
                    var filterWrap = $('.action-cancel');
                    if (filterWrap.length) {
                        clearInterval(test3);
                        filterWrap.hide()
                    }
                }, 50);

                var activeFilterBtn = setInterval(function() {
                    var filterAction = $('.data-grid-filters-action-wrap');
                    if (filterAction.length) {
                        clearInterval(activeFilterBtn);
                        filterAction.find('button').addClass('_active');
                    }
                }, 50);
            })
            
            // SETS GRID RENDER PARAMS
            var mainEntityCode = 'bricks_brand';

            var productSource = registry.get('product_form.product_form_data_source');
            var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
            var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';

            var setsGrid = this.attributeProvider();
            if (setsGrid) {
                setsGrid.set('params.' + mainEntityCode, mainEntityValue);
                setsGrid.set('params.is_singles', isHavingSingles ? 1 : 0);
                setsGrid.reload();
            }
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('Please select sets or create new one if not exist.');

            /*eslint-enable max-len*/

            // if (this.mode === 'edit') {
            //     this.wizard.setNotificationMessage(msg);
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

            var multiselect = this.multiselect()

            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';
            if (is_having_singles) {
                if (selected.length) {
                    selected.forEach(function(data, index) {
                        if (index != selected.length-1) {
                            multiselect.toggleSelect(data);
                            selected.splice(index, 1);
                        }
                    });
                }
            }
            
            this.selected = selected;
            if (!is_having_singles) {
                this.sets = [];
            }
            _.each(selected, function (entityId) {
                var set;

                // if (!this.attributesLabels[entityId]) {
                    set = _.findWhere(this.multiselect().rows(), {
                        'entity_id': entityId
                    });

                    if (set) {
                        if (!is_having_singles) {
                            this.sets.push(set);
                        } else {
                            this.sets(set);
                        }
                        this.attributesLabels[set['entity_id']] = set['bricks_set_name'] ?? 'Untitled';
                    }
                // }
                labels.push(this.attributesLabels[entityId]);
            }.bind(this));
            this.selectedSets(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.attributesIds = this.multiselect().selected;

            wizard.data.sets = this.sets[0];
            var setsData = this.sets[0];

            var _this = this;

            // if (!wizard.data.attributesIds() || wizard.data.attributesIds().length === 0) {
            //     throw new Error($.mage.__('Please select sets.'));
            // }

            var setsMatrixField = registry.get('product_form.product_form.legosets_sets.sets-matrix');
            
            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            if (is_having_singles && wizard.data.attributesIds() && wizard.data.attributesIds().length != 0) {
                wizard.data.sets = this.sets();
                this.setNotificationMessage();
            } else {
                wizard.data.sets = this.sets[0];
                if (wizard.data.attributesIds() && wizard.data.attributesIds().length != 0) 
                {
                    var sellerId = productSource.data.product['seller_id'] ?? '';

                    var attrCode = 'bricks_brand';
                    var setName = setsData.bricks_set_name;
                    var setAbbrv = setsData.bricks_set_number;

                    var mainEntityValue = productSource.data.product[attrCode] ?? '';
                    var mainEntityField = registry.get(
                        'product_form.product_form.category-details.container_' + attrCode + '.' + attrCode
                    );
                    
                    var options = mainEntityField.options._latestValue;
                    var mainEntityLabel = _this.getLabelByValue(options, mainEntityValue);

                    var productNameArr = [];
                    var productIdentifierArr = [];

                    if (setName) {
                        if (setAbbrv) {
                            productIdentifierArr.push(
                                _this.convertToSlug(setAbbrv).toUpperCase()
                            );
                            setName = setName + ' (' + setAbbrv + ')';
                        }
                        
                        productNameArr.push(setName);
                    }

                    if (mainEntityLabel) {
                        productNameArr.push(mainEntityLabel);
                    }

                    productIdentifierArr.push(
                        _this.makeid(10).toUpperCase()
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
                    urlKeyField.value(_this.convertToSlug(setName) + '-' + productIdentifier.toLowerCase());

                    var setsData = this.sets[0];
                    
                    var allImages = setsData.all_images;
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

                    setsMatrixField.visible(true);
                    
                    this.setsAndSinglesComponent().render(this.sets, null);
                } else {
                    setsMatrixField.visible(false);
                }
                
                this.modalComponent().closeModal();
            }
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
        convertToSlug: function(Text) {
          return Text.toLowerCase()
            .replace(/ /g, "-")
            .replace(/[^\w-]+/g, "");
        },
        checkURL: function(url) {
            return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
        },
        getLabelByValue: function(data, value) {
            const item = data.find(entry => entry.value === value);
            return item ? item.label : null;
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

        /**
         * Back.
         */
        back: function () {
        }
    });
});
