<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Export;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProcessExport extends \Magento\Framework\App\Action\Action
{
    protected $session;
    protected $sellerFactory;
    protected $jsonFactory;
    protected $stockRegistry;
    protected $csvProcessor;
    protected $fileSystem;
    protected $helper;
    protected $_frontendUrl;
    protected $queueFactory;


    protected $currentSeller = null;
    
    /**
     * @var LayoutInterface
     */
    protected $_layout;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;
    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutInterface $layout
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutInterface $layout,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context,$resultPageFactory,$coreRegistry);
        $this->_layout = $layout;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function getSellerId() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->session = $objectManager->get('\Magento\Customer\Model\Session'); 
                $this->sellerFactory = $objectManager->get('\Lof\MarketPlace\Model\SellerFactory'); 
        if (!$this->currentSeller && $this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $this->currentSeller = $this->sellerFactory->create()->load($customerId, 'customer_id');
        }
        return $this->currentSeller->getId();
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $requestParams = $this->getRequest()->getParams();

        $name = 'stock_data_'.date('Y-m-d');
        $file = 'export/stock/'. $name . '.csv';
        $allowedFields = [
            'sku',
            'quantity'
        ];

        $catId = $this->getRequest()->getParam('categories');
        $entity = $this->getRequest()->getParam('entity');

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestParams) {
            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->jsonFactory = $objectManager->get('\Magento\Framework\Controller\Result\JsonFactory'); 
                $this->stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface'); 
                $this->csvProcessor = $objectManager->get('\Magento\Framework\File\Csv'); 
                $this->fileSystem = $objectManager->get('\Magento\Framework\Filesystem'); 
                $this->helper = $objectManager->get('\TCGCollective\ProductAttributesLink\Helper\Data'); 
                $this->_frontendUrl = $objectManager->create(\Magento\Framework\UrlInterface::class);
                $this->queueFactory = $objectManager->create(\TCGCollective\MarketplaceProductImportExport\Model\ExportQueueFactory::class);
                
                $sellerId = $this->getSellerId();

                $resultJson = $this->jsonFactory->create();
                $resultJson->setHttpResponseCode(200);
                
                $params = [];

                if ($entity == "catalog_product") {
                    $model = $objectManager->create(\Magento\ImportExport\Model\Export::class);
                    $requestParams['export_filter']['category_ids'] = $requestParams['categories'];
                    // dd($requestParams);
                    $model->setData($requestParams);

                    // dd($model);

                    $fileName = $model->getFileName();
                    $exportData = $model->export();
                    // $params[] = $exportData;
                    $lines = explode(PHP_EOL, $exportData);
                    foreach ($lines as $line) {
                        $params[] = str_getcsv($line);
                    }
                    // dd($params);
                } else {
                    $productCollection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
                    ->addAttributeToSelect(['sku','name'])
                    ->addFieldToSelect([])
                    ->addAttributeToFilter('seller_id', $sellerId);

                    $subcategoriesAttrCode = $this->getRequest()->getParam('subcategories_attr_code');
                    $subcategoriesAttrLabel = $this->getRequest()->getParam('subcategories');
                    if ($subcategoriesAttrLabel) {
                        $subcategoriesAttrId = $this->helper->getAttributeOptionId($subcategoriesAttrCode, $subcategoriesAttrLabel);
                        if ($subcategoriesAttrId) {
                            $productCollection->addAttributeToFilter($subcategoriesAttrCode, $subcategoriesAttrId);
                        }
                    }

                    $subSubcategoriesAttrCode = $this->getRequest()->getParam('sub_subcategories_attr_code');
                    $subSubcategoriesAttrLabel = $this->getRequest()->getParam('sub_subcategories');
                    if ($subSubcategoriesAttrLabel) {
                        $subSubcategoriesAttrId = $this->helper->getAttributeOptionId($subSubcategoriesAttrCode, $subSubcategoriesAttrLabel);
                        if ($subSubcategoriesAttrId) {
                            $productCollection->addAttributeToFilter($subSubcategoriesAttrCode, $subSubcategoriesAttrId);
                        }
                    }
                    
                    if ($catId) {
                        $productCollection->addCategoriesFilter(['in' => $catId]);
                    }

                    if ($productCollection->getSize()) {
                        $params[] = ['sku','qty'];

                        

                        foreach ($productCollection as $product) {
                            $params[] = $product->getData();

                            $stockItem = $this->stockRegistry->getStockItem($product->getId());
                            if ($stockItem) {
                                $params[] = [
                                    'qty' => $stockItem->getQty()
                                ];
                            }
                        }
                    }

                    $fileName = 'product_stock_' . time() . '.csv';
                }

                $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
                $filePath = $varDirectory->getAbsolutePath($fileName);

                $fileUrl = null;
                $status = null;
                // die('<pre>'.print_r($params,true));
                if ($params) {
                    $this->csvProcessor->saveData($filePath, $params);

                    $fileUrl = $this->_frontendUrl->getUrl('*/export/download', ['filename' => $fileName]);
                    $status = 'success';
                }

                $categories = 'Unknown';
                if ($catId) {
                    $category = $this->helper->getCategoryById($catId);
                    if ($category) {
                        $categories = $category->getName();
                    }
                }

                $data = [
                    'seller_id' => $sellerId,
                    'categories' => $categories,
                    'entity' => $entity == "catalog_product" ? "product" : "stock",
                    'status' => $status,
                    'data_count' => $params ? count($params)-1 : 0,
                    'file' => $fileUrl
                ];

                $queue = $this->queueFactory->create();
                $queue->setData($data)
                ->save();

                $resultJson->setData([
                    'success' => true,
                    'data' => [
                        'file' => $fileUrl
                    ]
                ]);
        
                return $resultJson;
            } catch (\Exception $e) {
                dd($e->getMessage());
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                
            }
        }

        $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));
        
        // return $resultRedirect->setPath($refererUrl);
        return null;
    }
}
