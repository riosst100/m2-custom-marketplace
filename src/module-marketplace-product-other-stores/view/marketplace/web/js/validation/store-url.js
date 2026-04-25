define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/lib/validation/validator'
], function ($, $t, validator) {
    'use strict';

    /**
     * Tambahkan custom rule untuk validasi URL Shopee / Lazada
     */
    validator.addRule(
        'validate-store-url',
        function (value) {
            if ($.mage.isEmptyNoTrim(value)) {
                return true; // kosong = lolos
            }

            // Cari store name dari row yang sama
            var storeName = $('input[name^="product[link_to_other_stores]"][name$="[product_link]"]')
                .filter(function () {
                    return $(this).val() === value;
                })
                .closest('tr.data-row')
                .find('span[data-index="stores"]')
                .text()
                .trim()
                .toLowerCase();

            if (!storeName) {
                return true; // kalau gak ada store name, skip validasi
            }

            var isValid = true;

            if (storeName === 'shopee') {
                isValid = /^https?:\/\/(www\.)?shopee\.[a-z.]+\/.+/.test(value.trim());
            } else if (storeName === 'lazada') {
                isValid = /^https?:\/\/(www\.)?lazada\.[a-z.]+\/.+/.test(value.trim());
            }

            return isValid;
        },
        $t('Please enter a valid Shopee or Lazada product link.')
    );

    return validator;
});