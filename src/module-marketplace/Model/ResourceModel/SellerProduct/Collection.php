<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\SellerProduct;

use Lof\MarketPlace\Model\ResourceModel\SellerProduct\Collection as BaseCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;
use Magento\Store\Model\StoreManagerInterface;

class Collection extends BaseCollection
{
    protected AdminWebsite $adminWebsite;
    protected $storeManager;

    public function __construct(
        AdminWebsite $adminWebsite,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->adminWebsite = $adminWebsite;
        $this->storeManager = $storeManager;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
    }

    /**
     * Fix store filter → translate to website filter
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store') {
            $storeId = is_array($condition)
                ? (int) reset($condition)
                : (int) $condition;

            try {
                $store = $this->storeManager->getStore($storeId);
                $websiteId = (int) $store->getWebsiteId();
            } catch (\Exception $e) {
                return $this;
            }

            $this->joinProductWebsiteOnce();
            $this->getSelect()->where('cpw.website_id = ?', $websiteId);

            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Apply website restriction + EAV joins
     */
    protected function _renderFiltersBefore()
    {
        // Required for product name
        $this->joinEavAttributeTable();

        // Super admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            parent::_renderFiltersBefore();
            return;
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        // Safety: no access → no result
        if (empty($allowedWebsiteIds)) {
            $this->getSelect()->where('1 = 0');
            parent::_renderFiltersBefore();
            return;
        }

        $this->joinProductWebsiteOnce();
        $this->getSelect()->where(
            'cpw.website_id IN (?)',
            $allowedWebsiteIds
        );

        parent::_renderFiltersBefore();
    }

    /**
     * Join catalog_product_website ONCE only
     */
    protected function joinProductWebsiteOnce(): void
    {
        if ($this->getFlag('cpw_joined')) {
            return;
        }

        $this->getSelect()->join(
            ['cpw' => $this->getTable('catalog_product_website')],
            'cpw.product_id = main_table.product_id',
            []
        );

        $this->setFlag('cpw_joined', true);
    }
}
