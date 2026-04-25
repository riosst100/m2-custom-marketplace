<?php

namespace TCGCollective\MarketplacePaymentXendit\Model\Config\Source;

class Allowadmin implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Show Error message')
            ],
            [
                'value' => 1,
                'label' => __('Apply Admin Rates')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Show Error message'), 1 => __('Apply Admin Rates')];
    }
}
