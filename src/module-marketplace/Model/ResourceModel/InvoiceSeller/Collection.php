<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\InvoiceSeller;

use Lof\MarketPlace\Model\ResourceModel\Invoice\Grid\Collection as BaseCollection;
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
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        RequestInterface $request,
        AdminWebsite $adminWebsite,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->request = $request;
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model,
            $connection,
            $resource
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        // Super admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $this;
        }

        $websiteId = (int) $this->request->getParam('website');
        if (!$websiteId) {
            return $this;
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        // Prevent URL tampering
        if (!in_array($websiteId, $allowedWebsiteIds, true)) {
            $this->getSelect()->where('1 = 0');
            return $this;
        }

        // Join store table USING invoice_grid.store_id
        $this->getSelect()
            ->join(
                ['store' => $this->getTable('store')],
                'store.store_id = invoice_grid.store_id',
                []
            )
            ->where(
                'store.website_id = ?',
                $websiteId
            );

        return $this;
    }
}
