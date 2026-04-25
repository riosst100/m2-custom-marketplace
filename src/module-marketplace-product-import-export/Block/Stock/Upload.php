<?php

namespace TCGCollective\MarketplaceProductImportExport\Block\Stock;

use Magento\Catalog\Model\Category as CategoryModel;

class Upload extends \Magento\Framework\View\Element\Template
{
    protected $_frontendUrl;
    protected $historyFactory;
    protected $categoryCollectionFactory;
    protected $sellerHelper;
    protected $storeManager;
    protected $timezone;


    const IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE = '*/product/bulkimporthistorydownload';

    /**
     * @var
     */
    protected $currentSeller = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Lof\MarketPlace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * Export constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->session = $customerSession;
        $this->sellerFactory = $sellerFactory;
    }

    public function createDownloadUrlImportHistoryFile($fileName)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_frontendUrl = $objectManager->create(\Magento\Framework\UrlInterface::class);
        return $this->_frontendUrl->getUrl(self::IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE, ['filename' => $fileName]);
    }

    public function getSeller() 
    {
        if (!$this->currentSeller && $this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $this->currentSeller = $this->sellerFactory->create()->load($customerId, 'customer_id');
        }
        return $this->currentSeller;
    }

    public function getImportHistory($entity, $currentPage = 1, $pageSize = 5) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->historyFactory = $objectManager->create(\TCGCollective\MarketplaceProductImportExport\Model\ImportQueueFactory::class);

        $collection = $this->historyFactory->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $this->getSeller()->getId())
        ->setOrder('entity_id', 'DESC');

        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);
        if ($collection->getSize()) {
            return $collection;
        }

        return null;
    }

    public function getTotalImportHistoryCount($entity)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->historyFactory = $objectManager->create(\TCGCollective\MarketplaceProductImportExport\Model\ImportQueueFactory::class);

        return $this->historyFactory->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $this->getSeller()->getId())
        ->getSize();
    }

    public function getCategoriesData() 
    {
        $data = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->categoryCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class);
        $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Seller::class);
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

        $seller = $this->sellerHelper->getSeller();
        $storeId = null;
        if ($seller->getData('store_id')) {
            foreach ($seller->getData('store_id') as $key => $id){
                $storeId = $id;
            }
        }
        if(!$storeId){
            $storeId = $this->storeManager->getStore()->getId();
        }

        $storeRootCategory = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $matchingNamesCollection = $this->categoryCollectionFactory->create();
        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID]);

        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($matchingNamesCollection as $category) {
            if (
                ($storeRootCategory == CategoryModel::ROOT_CATEGORY_ID) ||
                str_contains($category->getPath(), '/'.$storeRootCategory.'/') ||
                str_ends_with($category->getPath(), '/'.$storeRootCategory)
            ){
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'hide_on_product_creation'])
            ->setStoreId($storeId);
        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        $collection->setOrder('position', 'ASC');
        if ($collection->getSize()) {
            foreach ($collection as $category) {
                if ($category->getHideOnProductCreation()) {
                    continue;
                }
                if ($category->getLevel() > 3) {
                    continue;
                }
                if (!$category->getIsActive()) {
                    continue;
                }

                $categoryById[$category->getId()]['text'] = $category->getName();
                $categoryById[$category->getId()]['id'] = $category->getId();
                $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
            }
        }

        $data = $categoryById[CategoryModel::TREE_ROOT_ID]['children'][0]['children'];

        return !empty($data) ? json_encode(array_values($data)) : [];
    }

    public function getUploadStatus($summary, $validationSummary = null) 
    {
        $data = [];

        if ($summary) {
            $summaryArr = explode(', ', $summary);
            foreach ($summaryArr as $summaryItem) {
                $summaryItemArr = explode(': ', $summaryItem);
                if ($summaryItemArr[0] == 'Created') {
                    $data['created'] = (int)$summaryItemArr[1];
                }
                if ($summaryItemArr[0] == 'Updated') {
                    $data['updated'] = (int)$summaryItemArr[1];
                }
                if ($summaryItemArr[0] == 'Deleted') {
                    $data['deleted'] = (int)$summaryItemArr[1];
                }
            }
        }

        if ($validationSummary) {
            $summaryArr = explode(', ', $validationSummary);
            foreach ($summaryArr as $summaryItem) {
                $summaryItemArr = explode(': ', $summaryItem);
                if ($summaryItemArr[0] == 'Error') {
                    $data['error'] = (int)$summaryItemArr[1];
                }
            }
        }

        // dd($data);

        return $data;
    }

    public function formatDateTime($dateTime)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->timezone = $objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');

        $dateTimeZone = $this->timezone->date(new \DateTime($dateTime))->format('Y-m-d H:i:s');


        return $dateTimeZone;
    }
}
