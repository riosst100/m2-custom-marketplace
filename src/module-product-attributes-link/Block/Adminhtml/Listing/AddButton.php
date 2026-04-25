<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Listing;

use \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Import Log'),
            'class' => 'primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'logger_log_listing.logger_log_listing.import_modal',
                                'actionName' => 'openModal'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 20
        ];
    }
}