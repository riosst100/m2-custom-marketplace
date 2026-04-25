define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'underscore'
], function (Modal, registry, _) {
    'use strict';

    return function(Modal) {
        return Modal.extend({
            /**
             * Open modal
             */
            openModal: function () {
                this._super();

                var stepWizard = {};

                console.log('ok')

                console.log(this.stepWizard)

                // this.form().validate();

                // if (this.form().source.get('params.invalid') === false) {
                    stepWizard = registry.get('index = ' + this.stepWizard);

                console.log(stepWizard)

                    if (!_.isUndefined(stepWizard)) {
                        stepWizard.open();
                    }

                    
                // }
            }
        });
    }
});
