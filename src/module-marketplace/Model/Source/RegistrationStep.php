<?php

namespace TCGCollective\MarketPlace\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RegistrationStep implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];

        $availableOptions = $this->getOptions();

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }

    public function getOptions()
    {
        $availableOptions = [
            'verification' => 'Verification',
            'payment' => 'Setup Payment Method',
            'membership' => 'Select Plan',
            'shipping' => 'Setup Shipping Method',
            'finish' => 'Verified'
        ];

        return $availableOptions;
    }
}
