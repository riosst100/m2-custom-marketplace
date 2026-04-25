<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Generic;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Upload implements ButtonProviderInterface 
{
    public function getButtonData() 
    {
        return [
            'label' => __('Start Upload'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}