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

    /**
     * @param {Function} provider
     */
    var initNewSinglesListener = function (provider) {
        $('[data-role=product-miniaturegame-sets-matrix]').on('addSingles', function () {
            provider().reload();
        });
    };

    return Component.extend({
        singlesLabels: {},
        stepInitialized: false,
        defaults: {
            modules: {
                setsAndSinglesComponent: '${ $.setsAndSinglesComponent }',
                modalComponent: '${ $.modalComponent }',
                multiselect: '${ $.multiselectName }',
                singlesProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedSinglesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedSingles: [],
            attributes: [],
            singles: [],
            sets: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];
                
            $(document).find('input[type=file]').fileupload({
                dataType: 'json',
                formData: {
                    'form_key': window.FORM_KEY
                },
                sequentialUploads: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
            });
            
            initNewSinglesListener(this.singlesProvider);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedSingles','attributes', 'singles','sets']);

            return this;
        },
        variations: [],
        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.sets(wizard.data.sets);
            this.setNotificationMessage();

            var singlesGrid = this.singlesProvider();

            var set = wizard.data.sets;

            singlesGrid.set('params.set_id', set.entity_id);
            singlesGrid.reload();
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('Please select singles or create new one if not exist.');

            /*eslint-enable max-len*/

            if (this.mode === 'edit') {
                // this.wizard.setNotificationMessage(msg);
            }
        },

        /**
         * Do select saved attributes.
         */
        doSelectSavedAttributes: function () {
            // if (this.stepInitialized === false) {
            //     this.stepInitialized = true;
            //     //cache attributes labels, which can be present on the 2nd page
            //     _.each(this.initData.attributes, function (attribute) {
            //         this.singlesLabels[attribute.id] = attribute.label;
            //     }.bind(this));
            //     this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            // }
        },

        /**
         * @param {*} selected
         */
        doSelectedSinglesLabels: function (selected) {
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
            this.singles = [];
            _.each(selected, function (entityId) {
                var singles;

                // if (!this.singlesLabels[entityId]) {
                    singles = _.findWhere(this.multiselect().rows(), {
                        'entity_id': entityId
                    });                    

                    if (singles) {
                        this.singles.push(singles);
                        this.singlesLabels[singles['entity_id']] = singles['figure_name'];
                    }
                // }
                labels.push(this.singlesLabels[entityId]);
            }.bind(this));
            this.selectedSingles(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.singlesId = this.multiselect().selected;
            
            if (!wizard.data.singlesId() || wizard.data.singlesId().length === 0) {
                throw new Error($.mage.__('Please select singles.'));
            }

            var singlesData = this.singles[0];
            var setsData = this.sets();

            var setsMatrixField = registry.get('product_form.product_form.cardgame_sets.sets-matrix');
            var singlesMatrixField = registry.get('product_form.product_form.cardgame_sets.singles-matrix');
            
            if (wizard.data.singlesId() && wizard.data.singlesId().length != 0) 
            {
                wizard.data.singles = singlesData;

                var allImages = singlesData.all_images;
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

                var productSource = registry.get('product_form.product_form_data_source');
                
                var sellerId = productSource.data.product['seller_id'] ?? '';
                            
                var attrCode = 'miniature_game';
                var cardName = singlesData.figure_name;
                var cardNumber = singlesData.figure_number;
                var setName = setsData.miniature_set;
                var setAbbrv = setsData.miniature_set_abbreviation;

                var mainEntityValue = productSource.data.product[attrCode] ?? '';
                var mainEntityField = registry.get(
                    'product_form.product_form.category-details.container_' + attrCode + '.' + attrCode
                );
                
                var options = mainEntityField.options._latestValue;
                var mainEntityLabel = this.getLabelByValue(options, mainEntityValue);
                
                var productNameArr = [];
                var productIdentifierArr = [];

                if (cardName) {
                    if (cardNumber) {
                        productIdentifierArr.push(
                            this.convertToSlug(cardNumber).toUpperCase()
                        );
                        cardName = cardName + ' ' + '#' + cardNumber;
                    }
                    
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
                urlKeyField.value(this.convertToSlug(cardName) + '-' + productIdentifier.toLowerCase());

                singlesMatrixField.visible(true);
            } else {
                singlesMatrixField.visible(false);
            }

            setsMatrixField.visible(true);
            
            this.setsAndSinglesComponent().render(this.sets(), this.singles);
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
