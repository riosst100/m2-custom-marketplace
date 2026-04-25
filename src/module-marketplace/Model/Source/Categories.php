<?php

namespace TCGCollective\MarketPlace\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Categories implements OptionSourceInterface
{
    /**
     * @var \Lof\MarketPlace\Model\Seller
     */
    protected $sellerModel;

    /**
     * Constructor
     *
     * @param \Lof\MarketPlace\Model\Seller $sellerModel
     */
    public function __construct(\Lof\MarketPlace\Model\Seller $sellerModel)
    {
        $this->sellerModel = $sellerModel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->sellerModel->getAvailableStatuses();

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
