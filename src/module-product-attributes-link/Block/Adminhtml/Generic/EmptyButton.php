<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Generic;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class EmptyButton implements ButtonProviderInterface 
{
    public function getButtonData() 
    {
        return [
            'label' => __('Save'),
            'hidden' => true,
            'class' => 'save primary hidden',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save'
            ],
            'sort_order' => 90,
        ];
    }
}