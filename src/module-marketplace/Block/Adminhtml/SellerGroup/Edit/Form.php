<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\SellerGroup\Edit;

use Magento\Customer\Block\Adminhtml\Group\Edit\Form as CustomerForm;

class Form extends CustomerForm
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $form = $this->getForm();

        // Force seller flag
        $form->addField(
            'is_seller',
            'hidden',
            [
                'name'  => 'is_seller',
                'value' => 1
            ]
        );

        // --- TAX CLASS ---
        $fieldset = $form->getElement('base_fieldset');
        if ($fieldset) {
            if ($form->getElement('tax_class_id')) {
                // get current value
                $value = $form->getElement('tax_class_id')->getValue();

                // remove visible field
                $fieldset->removeField('tax_class_id');

                // re-add as hidden
                $fieldset->addField(
                    'tax_class_id',
                    'hidden',
                    [
                        'name'  => 'tax_class',
                        'value' => $value
                    ]
                );
            }

            // --- EXCLUDED WEBSITES ---
            if ($form->getElement('customer_group_excluded_website_ids')) {
                $value = $form->getElement('customer_group_excluded_website_ids')->getValue();

                $fieldset->removeField('customer_group_excluded_website_ids');

                $fieldset->addField(
                    'customer_group_excluded_website_ids',
                    'hidden',
                    [
                        'name'  => 'customer_group_excluded_websites',
                        'value' => is_array($value) ? implode(',', $value) : $value
                    ]
                );
            }
        }        

        // IMPORTANT: change save URL
        $form->setAction($this->getUrl('marketplace/sellergroup/save'));

        return $this;
    }
}
