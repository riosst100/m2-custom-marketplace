<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\OrderSeller;

use Lof\MarketPlace\Model\ResourceModel\Order\Collection as BaseCollection;
use Magento\Framework\App\RequestInterface;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends BaseCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        RequestInterface $request,
        AdminWebsite $adminWebsite,
        $connection = null,
        $resource = null
    ) {
        $this->request = $request;
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        // Super admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return;
        }

        $websiteId = (int) $this->request->getParam('website');
        if (!$websiteId) {
            return;
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        // Hard guard (URL tampering protection)
        if (!in_array($websiteId, $allowedWebsiteIds, true)) {
            $this->getSelect()->where('1 = 0');
            return;
        }

        $this->getSelect()
            ->join(
                ['store' => $this->getTable('store')],
                'store.store_id = order_grid_table.store_id',
                []
            )
            ->where(
                'store.website_id = ?',
                $websiteId
            );
    }
}
