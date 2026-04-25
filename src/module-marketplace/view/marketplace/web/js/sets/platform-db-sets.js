// jscs:disable requireDotNotation
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'uiRegistry',
    'mage/translate'
], function (Component, $, ko, _, alert, registry, $t) {
    'use strict';

    function UserException(message) {
        this.message = message;
        this.name = 'UserException';
    }
    
    UserException.prototype = Object.create(Error.prototype);

    return Component.extend({
        defaults: {
            opened: false,
            attributes: [],
            usedAttributes: [],
            attributeCodes: [],
            setsData: {},
            singlesData: {},
            productMatrix: [],
            variations: [],
            formSaveParams: [],
            productAttributes: [],
            disabledAttributes: [],
            fullAttributes: [],
            rowIndexToEdit: false,
            productAttributesMap: null,
            valueSets: [],
            valueSingles: [],
            valueSealedProducts: [],
            valueRelease: [],
            modules: {
                associatedProductGrid: '${ $.configurableProductGrid }',
                wizardButtonElement: '${ $.wizardModalButtonName }',
                formElement: '${ $.formName }'
            },
            links: {
                valueSets: '${ $.provider }:${ $.dataScopeSets }',
                valueSingles: '${ $.provider }:${ $.dataScopeSingles }',
                valueSealedProducts: '${ $.provider }:${ $.dataScopeSealedProducts }',
                valueRelease: '${ $.provider }:${ $.dataScopeRelease }',
                usedAttributes: '${ $.provider }:${ $.dataScopeAttributes }',
                setsData: '${ $.provider }:${ $.dataScopeSetsData }',
                singlesData: '${ $.provider }:${ $.dataScopeSinglesData }',
                sealedproductsData: '${ $.provider }:${ $.dataScopeSealedProductsData }',
                releaseData: '${ $.provider }:${ $.dataScopeReleaseData }',
                attributeCodes: '${ $.provider }:${ $.dataScopeAttributeCodes }'
            }
        },
        initialize: function () {
            this._super();

            this.disableConfigurableAttributes(this.productAttributes);
        },
        initObservable: function () {
            this._super().observe(
                'actions opened attributes productMatrix valueSingles valueSealedProducts valueRelease valueSets usedAttributes setsData releaseData sealedproductsData singlesData attributeCodes'
            );

            return this;
        },
        render: function (set, singles, sealedproducts = null, release = null) {
            this.handleValue(set, singles, sealedproducts, release);
        },
        handleValue: function (set, singles, sealedproducts = null, release = null) {
            var setsArr = [], singlesArr = [], sealedproductsArr = [], releaseArr = [];

            var releaseObj = null;
            var singlesObj = null;
            var setsObj = null;
            var sealedproductsObj = null;

            if (set) {
                if (!Array.isArray(set)) {
                    set = [set];
                }
                set.forEach((setsData, index) => {
                    setsObj = this.populateSetProductsData(setsData);
                    setsArr.push(setsObj);
                });
               
                this.setsData(setsArr);
            }

            if (sealedproducts) {
                sealedproducts.forEach((sealedproductData, index) => {
                    sealedproductsObj = this.populateSealedProductsData(sealedproductData);
                    sealedproductsArr.push(sealedproductsObj);
                });
               
                this.sealedproductsData(sealedproductsArr);
            }

            
            
            if (release) {
                releaseObj = this.populateReleaseData(release);
                releaseArr.push(releaseObj);
                this.releaseData(releaseObj);
            }
            
            if (singles) {
                if (!Array.isArray(singles)) {
                    singles = [singles];
                }
                singles.forEach((singlesData, index) => {
                    singlesObj = this.populateSinglesData(singlesData);
                    singlesArr.push(singlesObj);
                });
                
                this.singlesData(singlesArr);
            }
            
            this.valueSets(setsArr);
            this.valueSingles(singlesArr);
            
            // if (sealedproductsArr) {
            this.valueSealedProducts(sealedproductsArr);
            // }
            
            // if (releaseArr) {
            this.valueRelease(releaseArr);
            // }
        },
        populateSetProductsData: function(set) {
            var thumb = '';
            
            var allImages = set.all_images;
            if (allImages) {
                var str_array = allImages.split(',');
                
                thumb = str_array && str_array.length ? str_array[0] : '';
            }
            
            var setsObj = _.extend(set, {}, {
                productId: null,
                images: thumb,
                release_year: set.release_year,
                set_name: set.card_set,
                set_abbreviation: set.card_set_abbreviation,
                miniature_set: set.miniature_set,
                miniature_set_item_no: set.miniature_set_item_no,
                miniature_faction: set.miniature_faction,
                miniature_sub_faction: set.miniature_sub_faction,
                miniature_armies: set.miniature_armies,
                miniature_battlefield_role: set.miniature_battlefield_role
            });

            return setsObj;
        },
        populateSealedProductsData: function(sealedproducts) {
            var thumb = '';
                
            var allImages = sealedproducts.all_images;
            if (allImages) {
                var str_array = allImages.split(',');
                
                thumb = str_array && str_array.length ? str_array[0] : '';
            }

            var sealedproductsObj = _.extend(sealedproducts, {}, {
                productId: null,
                images: thumb,
                sealed_products: sealedproducts.sealed_products,
                language: sealedproducts.language
            });

            return sealedproductsObj;
        },
        populateReleaseData: function(release) {
            var thumb = '';
    
            var releaseObj = _.extend(release, {}, {
                productId: null,
                card_release: release.card_release,
                release_year: release.release_year
            });

            return releaseObj;
        },
        populateSinglesData: function(singles) {
            var thumb = '';
                
            var allImages = singles.all_images;
            if (allImages) {
                var str_array = allImages.split(',');
                
                thumb = str_array && str_array.length ? str_array[0] : '';
            }

            var singlesObj = _.extend(singles, {}, {
                productId: null,
                images: thumb,
                card_name: singles.card_name,
                card_number: singles.card_number,
                card_rarity: singles.card_rarity,
                language: singles.language
            });

            return singlesObj;
        },
        disableConfigurableAttributes: function (attributes) {}
    });
});
