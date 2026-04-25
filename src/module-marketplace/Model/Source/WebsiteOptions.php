<?php
namespace TCGCollective\MarketPlace\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteOptions implements OptionSourceInterface
{
    public function __construct(
        private StoreManagerInterface $storeManager
    ) {}

    public function toOptionArray()
    {
        $options[] = ['value' => 0, 'label' => __('All Websites')];

        foreach ($this->storeManager->getWebsites() as $website) {
            $options[] = [
                'value' => (int)$website->getId(),
                'label' => $website->getName()
            ];
        }

        return $options;
    }
}
