<?php

namespace TCGCollective\MarketplaceProductPreorder\Model\Attribute\Source\PreOrder;

class PaymentType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_options;

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        $this->_options = [
            [
                'label' => __('Full Payment'),
                'value' => 'full'
            ],
            [
                'label' => __('Deposit'),
                'value' => 'deposit'
            ]
        ];

        return $this->_options;
    }
}
