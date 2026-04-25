define(['ko'], function (ko) {
    'use strict';

    ko.bindingHandlers.visibility = {
        update: function (element, valueAccessor) {
            var value = ko.unwrap(valueAccessor()); // Get the observable's value
            element.style.visibility = value ? 'visible' : 'hidden'; // Update the visibility property
        }
    };
});