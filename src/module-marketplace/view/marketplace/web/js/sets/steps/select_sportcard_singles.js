define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/translate',
    'platformDatabaseUtils'
], function (Component, $, _, registry, $t, platformDatabaseUtils) {
    'use strict';

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
            singles: [],
            sets: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            
            this.selected = [];
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedSingles', 'singles','sets']);

            return this;
        },
        variations: [],
        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.sets(wizard.data.sets);

            // SETS GRID RENDER PARAMS
            var mainEntityCode = 'sc_categories';

            var productSource = registry.get('product_form.product_form_data_source');
            var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
            var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';

            var listingGridProvider = this.singlesProvider();
            if (listingGridProvider) {
                let currentParams = listingGridProvider.get('params') || {};

                let newParams = Object.assign({}, currentParams, {
                    [mainEntityCode]: mainEntityValue,
                    is_singles: isHavingSingles ? 1 : 0
                });

                newParams.grid_filters = {};
                
                if (productSource) {
                    let scSetType = productSource.data.product['sc_set_type'];
                    if (scSetType) {
                        newParams.grid_filters['sc_set_type'] = platformDatabaseUtils.getOptionLabel('sc_set_type', scSetType);
                    }

                    var set = wizard.data.sets;
                    if (set) {
                        newParams.set_id = set.entity_id;
                    }
                }

                listingGridProvider.set('params', newParams);

                platformDatabaseUtils.updateFilters(newParams, wizard);
            }
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

                singles = _.findWhere(this.multiselect().rows(), {
                    'entity_id': entityId
                });                    

                if (singles) {
                    this.singles.push(singles);
                    this.singlesLabels[singles['entity_id']] = singles['sc_card_name'];
                }
                
                labels.push(this.singlesLabels[entityId]);
            }.bind(this));
            
            this.selectedSingles(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.singlesId = this.multiselect().selected;
            
            // if (!wizard.data.singlesId() || wizard.data.singlesId().length === 0) {
            //     throw new Error($.mage.__('Please select singles.'));
            // }

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
                            
                var attrCode = 'sc_categories';
                var cardName = singlesData.sc_card_name;
                var cardNumber = singlesData.sc_card_number;
                var setName = setsData.sc_set_name;
                var insertSetName = setsData.sc_insert_set_name;

                var mainEntityValue = productSource.data.product[attrCode] ?? '';
                var mainEntityField = registry.get(
                    'product_form.product_form.category-details.container_' + attrCode + '.' + attrCode
                );
                
                var options = mainEntityField.options._latestValue;
                var mainEntityLabel = this.getLabelByValue(options, mainEntityValue);
                
                var setAbbrv = null;
                var productNameArr = [];
                var productIdentifierArr = [];

                var productIdentifier = this.makeid(10).toUpperCase();

                // Set product name
                var productNameField = registry.get('product_form.product_form.product-details.container_name.name');
                productNameField.value(productIdentifier);

                // Set sku
                var sellerSkuField = registry.get('product_form.product_form.product-details.container_supplier_sku.supplier_sku');
                sellerSkuField.value(productIdentifier);

                var skuField = registry.get('product_form.product_form.product-details.container_sku.sku');
                skuField.value(productIdentifier);

                // Set url key
                var urlKeyField = registry.get('product_form.product_form.search-engine-optimization.container_url_key.url_key');
                urlKeyField.value(productIdentifier.toLowerCase());
                
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
