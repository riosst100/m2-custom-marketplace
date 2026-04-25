/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'uiRegistry',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component, registry, $, _) {
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
            sets: [],
            drop: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedSealedProducts','attributes', 'sealedProducts','drop','sets']);

            return this;
        },
        variations: [],
        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.sets(wizard.data.sets);
            this.drop(wizard.data.drop);
            // this.setNotificationMessage();

            var currentIndex = wizard.index;
                        var newIndex = currentIndex+1;
                        var switchIndex = newIndex+1;
                        
                        var nextStep = wizard.getStep(newIndex);
                        if (nextStep) {
                            if (nextStep.index !=  "select-secretlair-sets-steps-wizard_step.sealedproducts" && nextStep.index != "select-secretlair-sets-steps-wizard_step3") {
                                var steps = wizard.steps;
                                if (steps) {
                                    var b = steps[newIndex];
                                    steps[newIndex] = steps[switchIndex];
                                    steps[switchIndex] = b;
                                }
                                wizard.steps = steps;
                            }
                        }

            var sealedProductsGrid = this.sealedProductsProvider();

            var set = wizard.data.sets;
            if (set) {
                sealedProductsGrid.set('params.set_id', set.entity_id);
            }

            var drop = wizard.data.drop;
            if (drop) {
                sealedProductsGrid.set('params.card_drop', drop.entity_id);
            }

            console.log('wizard.steps')
            console.log(wizard.steps)
            
            sealedProductsGrid.reload();
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
                        this.sealedProductsLabels[sealedProducts['entity_id']] = sealedProducts['sealed_products'];
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
            var setsData = this.sets();
            
            var setsMatrixField = registry.get('product_form.product_form.cardgame_sets.sets-matrix');
            var sealedProductsMatrixField = registry.get('product_form.product_form.cardgame_sets.sealedproducts-matrix');

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
                        
            var attrCode = 'card_game';
            var cardName = sealedProductsData ? sealedProductsData.sealed_products : null;
            var cardNumber = sealedProductsData ? sealedProductsData.card_number : null;
            var setName = setsData ? setsData.card_set : null;
            var setAbbrv = setsData ? setsData.card_set_abbreviation : null;

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

            // var newIndex = 0;
            
            // wizard.move(newIndex);
            // wizard.cleanErrorNotificationMessage();
            // wizard.index = newIndex;
            // wizard.cleanNotificationMessage();
            // wizard.render();
            var currentIndex = wizard.index;
                        var newIndex = currentIndex+1;
                        var switchIndex = 0;

            console.log('currentIndex'+currentIndex)
            console.log('newIndex'+newIndex)
             var nextStep = wizard.getStep(newIndex);
            if (nextStep && nextStep.index ==  "select-secretlair-sets-steps-wizard_step2") {
                console.log(nextStep)
                var steps = wizard.steps;
                if (steps) {
                    var b = steps[newIndex];
                    steps[newIndex] = steps[switchIndex];
                    steps[switchIndex] = b;
                }
                wizard.steps = steps;
            }

            var firstStep = wizard.getStep(0);
            wizard.selectedStep = firstStep;
            wizard.index = 0;

            document.body.style.counterReset = "i";

            console.log('wizard')
            console.log(wizard)
            // wizard.close()
            // console.log('this.stepsNames')
            // console.log(wizard.stepsNames)
            // console.log('this.stepsNames.first()')
            // console.log(wizard.stepsNames.first())
            // wizard.cleanErrorNotificationMessage();
            // wizard.index = 0;
            // wizard.cleanNotificationMessage();
            // wizard.render();
            // var currentIndex = wizard.index;
            //             var newIndex = currentIndex+1;
            //             var switchIndex = newIndex+1;
                        
            //             var nextStep = wizard.getStep(newIndex);
            //             if (nextStep && nextStep.index !=  "select-secretlair-sets-steps-wizard_step.sealedproducts") {
            //                 var steps = wizard.steps;
            //                 if (steps) {
            //                     var b = steps[newIndex];
            //                     steps[newIndex] = steps[switchIndex];
            //                     steps[switchIndex] = b;
            //                 }
            //                 wizard.steps = steps;
            //             }
            
            this.setsAndSinglesComponent().render(this.sets(), null, this.sealedProducts, this.drop());
            this.modalComponent().closeModal();

            return;
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
        back: function () {
        }
    });
});
