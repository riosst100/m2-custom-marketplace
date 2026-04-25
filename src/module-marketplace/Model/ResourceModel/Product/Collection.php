<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as CoreCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CoreCollection
{
    protected AdminWebsite $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory $productLimitationFactory = null,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        \Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer $tableMaintainer = null,
        \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver $priceTableResolver = null,
        \Magento\Framework\Indexer\DimensionFactory $dimensionFactory = null,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel = null,
        \Magento\CatalogUrlRewrite\Model\Storage\DbStorage $urlFinder = null,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $productGalleryReadHandler = null,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $mediaGalleryResource = null,
        AdminWebsite $adminWebsite
    ) {
        $this->adminWebsite = $adminWebsite;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection,
            $productLimitationFactory,
            $metadataPool,
            $tableMaintainer,
            $priceTableResolver,
            $dimensionFactory,
            $categoryResourceModel,
            $urlFinder,
            $productGalleryReadHandler,
            $mediaGalleryResource
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();
        if (empty($websiteIds)) {
            return $this;
        }

        if (!$this->getFlag('admin_website_filter_applied')) {
            $this->getSelect()->join(
                ['cpw' => $this->getTable('catalog_product_website')],
                'cpw.product_id = e.entity_id',
                []
            );

            $this->addFieldToFilter('cpw.website_id', ['in' => $websiteIds]);
            $this->getSelect()->group('e.entity_id');

            $this->setFlag('admin_website_filter_applied', true);
        }

        return $this;
    }
}
