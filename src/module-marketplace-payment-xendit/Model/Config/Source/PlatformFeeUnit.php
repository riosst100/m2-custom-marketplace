<?php

namespace TCGCollective\MarketplacePaymentXendit\Model\Config\Source;

class PlatformFeeUnit implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'percent',
                'label' => __('PERCENT')
            ],
            [
                'value' => 'fixed',
                'label' => __('FIXED')
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
        return ['percent' => __('PERCENT'), 'fixed' => __('FIXED')];
    }
}
