<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Quickrfq\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Lof\Quickrfq\Model\ResourceModel\Quickrfq\Collection as BaseCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;
use Magento\Framework\App\RequestInterface;

class Collection extends BaseCollection
{
    protected $_eventPrefix;
    protected $_eventObject;

    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var AdminWebsite
     */
    protected $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RequestInterface $request,
        AdminWebsite $adminWebsite,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->request = $request;
        $this->adminWebsite = $adminWebsite;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        return $this;
    }
    
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        // Super admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            $this->getSelect()->joinLeft(
                    ['so' => $this->getTable('sales_order')],
                    'so.entity_id = main_table.order_id',
                    ['store_id']
            );
            $this->getSelect()
                ->join(
                    ['store' => $this->getTable('store')],
                    'store.store_id = so.store_id',
                    []
                );
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

        $this->getSelect()->joinLeft(
                ['so' => $this->getTable('sales_order')],
                'so.entity_id = main_table.order_id',
                ['store_id']
        );
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
    }
}
