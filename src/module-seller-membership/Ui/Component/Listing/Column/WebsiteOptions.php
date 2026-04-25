<?php
namespace TCGCollective\SellerMembership\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;

class WebsiteOptions implements OptionSourceInterface
{
    protected $storeCollectionFactory;

    public function __construct(
        StoreCollectionFactory $storeCollectionFactory
    ) {
        $this->storeCollectionFactory = $storeCollectionFactory;
    }

    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storesHelper = $objectManager->create(\TCGCollective\MarketPlace\Helper\Stores::class);

        $options = $this->storesHelper->getStoresOptionsArray();

        // Reindex array if numeric keys are required
        $options = array_values($options);

        return $options;
    }
}
