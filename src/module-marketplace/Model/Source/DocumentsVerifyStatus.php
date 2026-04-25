<?php

namespace TCGCollective\MarketPlace\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DocumentsVerifyStatus implements OptionSourceInterface
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
            1 => 'Verified',
            2 => 'Need Review',
            3 => 'Rejected'
        ];

        return $availableOptions;
    }
}
