/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/url',
    'platformDatabaseUtils',
    'mage/translate'
], function (Component, $, _, registry, urlBuilder, platformDatabaseUtils) {
    
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-cardgame-release-matrix]').on('add', function () {
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
            selectedRelease: [],
            release: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        
        initObservable: function () {
            this._super().observe(['selectedRelease','release']);

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            // this.setNotificationMessage();

            console.log('RENDER | SETS STEP')

            platformDatabaseUtils.hideSteps(wizard);

//             var productSource = registry.get('product_form.product_form_data_source');
//             var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

//             var stepReleaseLink = $('a[href="#select-cardgame-sets-steps-wizard_step1"');
//             if (stepReleaseLink) {
//                 var stepRelease = stepReleaseLink.parent();
//                 if (stepRelease) {
//                     if (is_having_singles) {
//                         stepRelease.hide();
//                     } else {
//                         stepRelease.show();
//                     }
//                 }
//             }
            
//             // if (is_having_singles) {
//             //     wizard.getStep().next();
//             //     // this.setNotificationMessage();
//             // }
//             var is_sealedproducts = productSource ? productSource.data.product['is_sealedproducts'] : '';
//             var card_game_mtg = productSource ? productSource.data.product['card_game_mtg'] : '';
// console.log('is_having_singles'+is_having_singles)
//             // if (!is_having_singles) {
//             // var step2link = $('a[href="#select-cardgame-release-steps-wizard_step2"');
//             // if (step2link) {
//             //     var step2 = step2link.parent();
//             //     step2.hide();
//             // }

//             // if (card_game_mtg == 24820) {
                
//             // }
            
//             // var step2link = $('a[href="#select-cardgame-release-steps-wizard_step2"');
//             // if (step2link) {
//             //     var step2 = step2link.parent();
//             //     if (step2) {
//             //         if (card_game_mtg == 24820) {
//             //             step2.show();
//             //         } else {
//             //             step2.hide();
//             //         }
//             //     }
//             // }

//             var firstStep = wizard.getStep(0);
//             wizard.selectedStep = firstStep;
//             wizard.index = 0;

//             // console.log('wizard')
//             // console.log(wizard)

            
            
//             // var step1link = $('a[href="#select-cardgame-sets-steps-wizard_step1"');
//             // if (step1link) {
//             //     var step1 = step1link.parent();
//             //     if (step1) {
//             //         if (is_sealedproducts) {
//             //             step1.hide();
//             //         } else {
//             //             step1.show();
//             //         }
//             //     }
//             // }

//             var step2link = $('a[href="#select-cardgame-sets-steps-wizard_step.release"');
//             if (step2link) {
//                 var step2 = step2link.parent();
//                 if (step2) {
//                     if (is_having_singles) {
//                         step2.hide();
//                     } else {
//                         step2.show();
//                     }
//                 }
//             }

//             var step3link = $('a[href="#select-cardgame-sets-steps-wizard_step.singles"');
//             if (step3link) {
//                 var step3 = step3link.parent();
//                 if (step3) {
//                     if (!is_having_singles) {
//                         step3.hide();
//                     } else {
//                         step3.show();
//                     }
//                 }
//             }

//             var stepsetslink = $('a[href="#select-cardgame-sets-steps-wizard_step.sets"');
//             if (stepsetslink) {
//                 var stepSets = stepsetslink.parent();
//                 if (stepSets) {
//                     if (!is_having_singles) {
//                         stepSets.hide();
//                     } else {
//                         stepSets.show();
//                     }
//                 }
//             }

//             var stepsealedproductslink = $('a[href="#select-cardgame-sets-steps-wizard_step.sealedproducts"');
//             if (stepsealedproductslink) {
//                 var stepsealedproducts = stepsealedproductslink.parent();
//                 if (stepsealedproducts) {
//                     if (is_having_singles) {
//                         stepsealedproducts.hide();
//                     } else {
//                         stepsealedproducts.show();
//                     }
//                 }
//             }

            

//             // var stepReleaseLink = $('a[href="#select-cardgame-sets-steps-wizard_step.release"');
//             // if (stepReleaseLink) {
//             //     var stepRelease = stepReleaseLink.parent();
//             //     if (stepRelease) {
//             //         if (is_having_singles) {
//             //             stepRelease.hide();
//             //         } else {
//             //             stepRelease.show();
//             //         }
//             //     }
//             // }

//             document.body.style.counterReset = "i";

//             var currentIndex = wizard.index;
//             var newIndex = currentIndex+1;
//             var switchIndex = newIndex+1;

            
            
//             // var nextStep = wizard.getStep(newIndex);
//             // if (nextStep) {
//             //     if (
//             //         !is_having_singles 
//             //         && nextStep.index == "select-cardgame-sets-steps-wizard_step.sets"
//             //     ) {
//             //         console.log('NEXT STEPPPPP')
//             //         var steps = wizard.steps;
//             //         if (steps) {
//             //         //     var b = steps[newIndex];
//             //             delete steps[newIndex];
//             //             steps[newIndex] = steps[switchIndex];
//             //         //     steps[switchIndex] = b;
                        
//             //             // steps[currentIndex] = steps[newIndex];
//             //             // steps[switchIndex] = b;
//             //             // steps[switchIndex] = b;
//             //         }
//             //         wizard.steps = steps;
//             //     }
//             // }

//             // var nextStep = wizard.index;

//             // console.log(wizard.steps)

//             // var nextStep = wizard.getStep(newIndex);
//             // if (nextStep) {
//             //     console.log('nextStep.indexnextStep.index')
//             //     console.log(nextStep.index)
//             //     if (
//             //         !is_having_singles 
//             //         && nextStep.index == "select-cardgame-sets-steps-wizard_step2"
//             //         || is_having_singles 
//             //         && nextStep.index == "select-cardgame-sets-steps-wizard_step.sealedproducts"
//             //     ) {
//             //         var steps = wizard.steps;
//             //         if (steps) {
//             //         //     var b = steps[newIndex];
//             //         //     steps[newIndex] = steps[switchIndex];
//             //         //     steps[switchIndex] = b;
//             //             steps[newIndex] = steps[switchIndex];
//             //             // steps[switchIndex] = b;
//             //             // steps[switchIndex] = b;
//             //             delete steps[switchIndex];
//             //         }
//             //         wizard.steps = steps;
//             //     }
//             // }
            
//             var stepsWizard = registry.get('product_form.product_form.cardgame.setsModal.wizard');
//             if (stepsWizard) {
//                 // stepsWizard.visible = false
//                 // var steps = stepsWizard.steps;
//                 // var stepsNames = stepsWizard.stepsNames;
//                 // var _elems = stepsWizard._elems;
                
//                 // var elems = stepsWizard.elems();

//                 // steps.splice(1, 1);
//                 // stepsNames.splice(1, 1);
//                 // elems.splice(1, 1);

//                 // var step2 = elems[1];

//                 // step2.disabled(true);

//                 // stepsWizard.steps = steps;
//                 // stepsWizard.stepsNames = stepsNames;
//                 // stepsWizard._elems = elems;
//                 // stepsWizard.elems = elems;
//             }

            
//             $(document).ready(function(){
//                 var test = setInterval(function() {
//                     var filterWrap = $('.admin__data-grid-filters-wrap');
//                     if (filterWrap.length) {
//                         clearInterval(test);
//                         filterWrap.css('padding','15px 0');
//                         filterWrap.find('button[data-action="grid-filter-cancel"]').hide();
//                         filterWrap.find('fieldset').css('padding','0px');
//                         $('.admin__data-grid-filters-wrap').addClass('_show');
//                     }
//                 }, 50);

//                 var test3 = setInterval(function() {
//                     var filterWrap = $('.action-cancel');
//                     if (filterWrap.length) {
//                         clearInterval(test3);
//                         filterWrap.hide()
//                     }
//                 }, 50);

//                 var activeFilterBtn = setInterval(function() {
//                     var filterAction = $('.data-grid-filters-action-wrap');
//                     if (filterAction.length) {
//                         clearInterval(activeFilterBtn);
//                         filterAction.find('button').addClass('_active');
//                     }
//                 }, 50);
//             });
            
//             // var productSource = registry.get('product_form.product_form_data_source');
//             // var card_game = productSource ? productSource.data.product['card_game'] : '';
//             // var card_game_mtg = productSource ? productSource.data.product['card_game_mtg'] : '';

//             // var releaseGrid = this.attributeProvider();
//             // if (releaseGrid) {
//             //     releaseGrid.set('params.card_game', card_game);
//             //     var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';
//             //     releaseGrid.set('params.is_singles', isHavingSingles ? 1 : 0);
//             //     releaseGrid.reload();
//             // }

//             // SETS GRID RENDER PARAMS
//             // var mainEntityCode = 'card_game';
//             // var subMainEntityCode = 'card_game_mtg';

//             // var productSource = registry.get('product_form.product_form_data_source');
//             // var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
//             // var subMainEntityValue = productSource ? productSource.data.product[subMainEntityCode] : '';
//             // var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';

//             // var releaseGrid = this.attributeProvider();
//             // if (releaseGrid) {
//             //     releaseGrid.set('params.' + mainEntityCode, mainEntityValue);
//             //     releaseGrid.set('params.' + subMainEntityCode, subMainEntityValue);
//             //     releaseGrid.set('params.is_singles', isHfavingSingles ? 1 : 0);
//             //     releaseGrid.reload();
//             // }
//             var mainEntityCode = 'card_game';

//             var productSource = registry.get('product_form.product_form_data_source');
//             var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
//             var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';
//             var card_release = wizard.data.release ?? null;

//             var setsGrid = this.attributeProvider();
//             console.log('setsGrid')
//             console.log(setsGrid)
//             if (setsGrid) {
//                 setsGrid.set('params.' + mainEntityCode, mainEntityValue);
//                 // setsGrid.set('params.is_singles', isHavingSingles ? 1 : 0);
//                 setsGrid.reload();
//             }
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            // var msg = $.mage.__('Please select release or create new one if not exist.');

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

            if (selected.length) {
                selected.forEach(function(data, index) {
                    if (index != selected.length-1) {
                        multiselect.toggleSelect(data);
                        selected.splice(index, 1);
                    }
                });
            }
            
            this.selected = selected;
            // if (!is_having_singles) {
                // this.release = [];
            // }
            _.each(selected, function (entityId) {
                var set;

                set = _.findWhere(this.multiselect().rows(), {
                    'entity_id': entityId
                });

                console.log('setttt')
                console.log(set)

                if (set) {
                    // if (!is_having_singles) {
                            // this.release.push(set);
                        // } else {
                            this.release(set);
                        // }
                    this.attributesLabels[set['entity_id']] = set['card_release'];
                }
                
                labels.push(this.attributesLabels[entityId]);
            }.bind(this));
            this.selectedRelease(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            var releaseMatrixField = registry.get('product_form.product_form.cardgame_release.release-matrix');
            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            if (!is_having_singles) {
            
            wizard.data.release = this.release();

                console.log(wizard.data.release)
            
            if (!this.selectedRelease()) {
                throw new Error($.mage.__('Please select release.'));
            }

            // Check is secret lair release
            var release = this.release();
            var releaseData = this.release();
            }
            
            var _this = this;
            
            
            
            _this.setNotificationMessage();
        },
        getLabelByValue: function(data, value) {
            const item = data.find(entry => entry.value === value);
            return item ? item.label : null;
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
        back: function () {
        }
    });
});
