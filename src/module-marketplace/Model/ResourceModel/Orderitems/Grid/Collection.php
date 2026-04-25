<?php
namespace TCGCollective\MarketPlace\Model\ResourceModel\Orderitems\Grid;

use Lof\MarketPlace\Model\ResourceModel\Orderitems\Grid\Collection as BaseCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;
use Magento\Framework\App\RequestInterface;

class Collection extends BaseCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        RequestInterface $request,
        AdminWebsite $adminWebsite,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class
    ) {
        $this->request = $request;
        $this->adminWebsite = $adminWebsite;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $localeDate,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        /** ----------------------------------------
         * JOIN sales_order to get store_id
         * --------------------------------------- */
        $this->getSelect()->join(
            ['so' => $this->getTable('sales_order')],
            'so.entity_id = main_table.order_id',
            ['store_id' => 'store_id'] // expose store_id to grid
        );

        /** ----------------------------------------
         * Restrict by admin website
         * --------------------------------------- */
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $this;
        }

        $websiteId = (int) $this->request->getParam('website');
        if (!$websiteId) {
            return $this;
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        // Block URL tampering
        if (!in_array($websiteId, $allowedWebsiteIds, true)) {
            $this->getSelect()->where('1 = 0');
            return $this;
        }

        $this->getSelect()
            ->join(
                ['store' => $this->getTable('store')],
                'store.store_id = so.store_id',
                []
            )
            ->where(
                'store.website_id = ?',
                $websiteId
            );

        return $this;
    }
}
