define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'underscore'
], function (Modal, registry, _) {
    'use strict';

    return function(Modal) {
        return Modal.extend({
            defaults: {
                stepWizard: '',
                modules: {
                    form: '${ $.formName }'
                }
            },

            /**
             * Open modal
             */
            openModal: function () {
                this._super();

                var stepWizard = {};

                console.log('ok')

                // this.form().validate();

                // if (this.form().source.get('params.invalid') === false) {
                    stepWizard = registry.get('index = ' + this.stepWizard);

                    if (!_.isUndefined(stepWizard)) {
                        stepWizard.open();
                    }

                    
                // }
            }
        });
    }
});
