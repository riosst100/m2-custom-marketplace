<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Catalog\Model\Product\Type as ProductTypes;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Lof\MarketPlace\Model\Source\Approval;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Controller\ResultFactory;

class CreateProductHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $legoSets;
    protected $legoSinglesFactory;
    protected $cardGameSetsCard;
    protected $cardGameSealedProducts;
    protected $cardGameSets;
    protected $livingCardGameSets;
    protected $miniatureGameSingles;
    protected $miniatureGameSets;
    protected $nonSportCardsSets;
    protected $sportCardsSinglesFactory;
    protected $sportCardsSetsFactory;
    protected $actionToysSetsFactory;
    protected $actionToysSinglesFactory;
    // END
    protected $_scopeConfig;
    protected $request;
    protected $rabbitmqQueuesFactory;
    protected $categoryRepository;
    protected $categoryLinkManagement;
    protected $productFactory;
    protected $productRepository;
    protected $mappingHelper;
    protected $publisher;
    protected $catalogHelper;
    protected $productAction;
    protected $sender;
    protected $seller;
    protected $initializationHelper;
    protected $productTypeManager;
    protected $helper;
    protected $storeManager;
    protected $notificationResource;
    protected $notificationFactory;
    protected $mainCategoriesConfigProvider;
    protected $sellerProductFactory;
    protected $flushCacheByIds;
    protected $websiteCollectionFactory;

    // Variable
    protected $sellerId = null;
    protected $isRabbitMq = false;
    protected $storeId = null;
    protected $productData = null;
    protected $singlesDataArr = null;
    protected $setsDataArr = null;
    protected $sealedProductsDataArr = null;
    protected $customTableType = null;
    protected $customTableDataArr = null;
    protected $messageManager = null;
    protected $categoryId = null;
    protected $prefix = null;
    protected $customTableMetadata = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \TCGCollective\MarketPlace\Model\RabbitmqQueuesFactory $rabbitmqQueuesFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        PublisherInterface $publisher,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        ProductAction $productAction,
        \Lof\MarketPlace\Model\Sender $sender,
        \Lof\MarketPlace\Model\Seller $seller,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \TCGCollective\MarketPlace\Model\ResourceModel\SellerNotifications $notificationResource,
        \TCGCollective\MarketPlace\Model\SellerNotificationsFactory $notificationFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider,
        \Lof\MarketPlace\Model\SellerProductFactory $sellerProductFactory,
        \Magento\InventoryCache\Model\FlushCacheByProductIds $flushCacheByIds,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
    ) {
        parent::__construct(
            $context
        );

        $this->_scopeConfig = $context->getScopeConfig();   
        $this->request = $request;
        $this->rabbitmqQueuesFactory = $rabbitmqQueuesFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->mappingHelper = $mappingHelper;
        $this->publisher = $publisher;
        $this->catalogHelper = $catalogHelper;
        $this->productAction = $productAction;
        $this->sender = $sender;
        $this->seller = $seller;
        $this->initializationHelper = $initializationHelper;
        $this->productTypeManager = $productTypeManager;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->notificationResource = $notificationResource;
        $this->notificationFactory = $notificationFactory;
        $this->messageManager = $messageManager;
        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
        $this->sellerProductFactory = $sellerProductFactory;
        $this->flushCacheByIds = $flushCacheByIds;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
    }

    // controller create product process
    public function processCreateProduct() 
    {
        $this->sellerId = $this->helper->getSellerId();
        $this->storeId = $this->request->getParam('store', 0);
        $this->productData = $this->request->getPostValue('product') ?? null;

        $this->initCustomTableData();

        // Process to RabbitMQ for more than 1 products
        if (
            $this->setsDataArr && count($this->setsDataArr) > 1 
            || $this->singlesDataArr && count($this->singlesDataArr) > 1 
            || $this->sealedProductsDataArr && count($this->sealedProductsDataArr) > 1
        ) {
            return $this->addToRabbitmqQueues();
        }
        
        // Save products process
        return $this->processSaveProducts();
    }

    // rabbitmq create product process
    public function processCreateProductRabbitMq($rabbitMqMessage) 
    {
        $this->debug('processCreateProductRabbitMq | process started');
        $this->debug("processCreateProductRabbitMq | received message = " . $rabbitMqMessage);

        // decode rabbitmq message json to array
        $rabbitMqMessageData = json_decode($rabbitMqMessage, true);
        if (!$rabbitMqMessageData) {
            $this->debug("processCreateProductRabbitMq | Invalid message payload");
            return;
        }

        // message data
        $this->isRabbitMq = true;
        $this->sellerId = $rabbitMqMessageData['seller_id'] ?? null;
        $this->storeId = $rabbitMqMessageData['store_id'] ?? 0;
        $this->productData = $rabbitMqMessageData['data']['product'] ?? [];

        $saveData = $rabbitMqMessageData['data'] ?? [];
        $requestParams = $rabbitMqMessageData['build_payload'] ?? null;
        
        $this->initCustomTableData();

        // get queue model
        $queueId = $rabbitMqMessageData['queue_id'] ?? null;
        $queueModel = $this->getRabbitMqQueueById($queueId);
        if (!$queueModel) {
            return;
        }

        // set queue status to processing
        $queueModel->setStatus('processing')->save();

        $backupPost = $this->request->getPostValue() ?: [];
        $keysToBackup = [
            'product',
            'attributes',
            'configurable-matrix-serialized',
            'configurable_matrix_serialized',
            'new-variations-attribute-set-id',
            'set',
            'affect_configurable_product_attributes',
            'associated_product_ids_serialized',
            'configurable_attributes_data',
            'is_preview',
            'is_ajax'
        ];
        $backupParams = [];
        foreach ($keysToBackup as $k) {
            $backupParams[$k] = $this->request->getParam($k);
        }

        try {
            // Put payload into POST/params so Admin helpers/plugins read them
            if (is_array($saveData) && count($saveData)) {
                // this sets POST values (product, attributes, etc)
                $this->request->setPostValue($saveData);
            }

            // set all build payload entries as params (and as post when appropriate)
            foreach ($requestParams as $k => $v) {
                // set param (for getParam checks)
                $this->request->setParam($k, $v);

                // also ensure hyphen/underscore variants are present
                if (strpos($k, '_') !== false) {
                    $hyphenKey = str_replace('_', '-', $k);
                    $this->request->setParam($hyphenKey, $v);
                } elseif (strpos($k, '-') !== false) {
                    $underscoreKey = str_replace('-', '_', $k);
                    $this->request->setParam($underscoreKey, $v);
                }
            }

            // Some helpers expect product in post; ensure product array is set
            if (isset($saveData['product']) && is_array($saveData['product'])) {
                $this->request->setParam('product', $saveData['product']);
                $this->request->setPostValue(['product' => $saveData['product']]);
            }
            
            // Save products process
            $this->processSaveProducts();

            // Clear my products listing cache
            $this->catalogHelper->clearMyProductsCache();

            $queueModel->setStatus('done')->save();

            // Notify seller
            $this->addSellerNotification([
                'seller_id' => $this->sellerId,
                'is_read' => 0,
                'url_path' => 'catalog/queues/index',
                'title' => 'Add Product From Database',
                'message' => __("Your %1 products have been processed successfully.", count($this->customTableDataArr))
            ]);

            $this->debug("Consumer: Successfully processed seller_id=$this->sellerId");
        } catch (\Exception $e) {
            $this->debug("Consumer: Error " . $e->getMessage());
        }
    }

    // process custom table data
    public function processSaveProducts() 
    {
        $countSavedProducts = 0;

        if (!$this->productData) {
            $this->messageManager->addErrorMessage(__('No product data to save'));
            return $this->redirectToMyProducts();
        }

        try {
            // Modify media gallery to include images from custom table
            if (!isset($this->productData['current_product_id'])) {
                $this->modifyMediaGallery();
            }

            if ($this->singlesDataArr) {
                $this->customTableType = 'singles';
                $this->customTableDataArr = $this->singlesDataArr;
            } elseif ($this->sealedProductsDataArr) {
                $this->customTableType = 'sealedproducts';
                $this->customTableDataArr = $this->sealedProductsDataArr;
            } elseif ($this->setsDataArr) {
                $this->customTableType = 'sets';
                $this->customTableDataArr = $this->setsDataArr;
            }
            
            if ($this->customTableDataArr) {
                // Get custom table metadata
                $this->customTableMetadata = $this->mainCategoriesConfigProvider->getByCategoryId($this->categoryId);
                // dd($this->customTableMetadata);
                $this->debug("Consumer: customTableMetadata " . print_r($this->customTableMetadata,true));
                if (!$this->customTableMetadata) {
                    $this->messageManager->addErrorMessage(__('Custom table metadata not found.'));
                    return null;
                }

                $this->debug("Consumer: customTableDataArr " . print_r($this->customTableDataArr,true));

                foreach($this->customTableDataArr as $customTableData) {
                    if ($customTableData) {
                        $this->saveProduct($customTableData);

                        $countSavedProducts++;
                    }
                }
            }
            
            if (!$this->customTableDataArr) {
                $this->saveProduct();

                $countSavedProducts++;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            dd($e);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            dd($e);
        }

        // dd($countSavedProducts);
        
        $isPublish = $this->request->getParam('is_publish');
        if ($countSavedProducts > 0 && !$isPublish) {
            if ($countSavedProducts > 1) {
                $this->messageManager->addSuccessMessage(__('Your %1 products have been saved.', $countSavedProducts));
            } else {
                $this->messageManager->addSuccessMessage(__('Your product has been saved.'));
            }

            $this->messageManager->addNoticeMessage(__('It may take 1-2 minutes for the product to appear and reflect updates on the storefront.'));
        }

        return $this->redirectToMyProducts();
    }

    public function saveProduct(
        $customTableData = null,
    ) {
        $this->debug("Consumer:saveProduct  customTableData " . print_r($customTableData,true));

        $setsId = $this->singlesDataArr['entity_id'] ?? null;
        $singlesId = $singlesData['entity_id'] ?? null;
        $sealedProductsId = $sealedProductsData['entity_id'] ?? null;

        $product = $this->build();
        if (!$product) {
            $this->messageManager->addErrorMessage(__('Product is not built.'));
            return null;
        }
        
        if ($customTableData && !isset($this->productData['current_product_id']) && !isset($this->productData['configurable_attributes_data'])) {
            $customProductData = $this->setMediaGallery($customTableData);
            $this->request->setParam('product', $customProductData);
        }

        $product = $this->initializationHelper->initialize($product);

        // dd($customTableData);

        // Populate custom table data
        if ($customTableData) {
            $categoryId = $this->productData['category_ids'] ?? null;
            if (!$categoryId) {
                $this->messageManager->addErrorMessage(__('Category not found on some products.'));
                return null;
            }

            $product = $this->populateCustomTableData(
                $product,
                $customTableData
            );
        }

        $this->productTypeManager->processProduct($product);

        if ($product->isObjectNew()) {
            // Set status to draft for new products
            $product->setStatus('1');
            $product->setPublishStatus('2');
            $product->setApproval(Approval::STATUS_APPROVED);
            
            $websiteIds = [];
            
            $product->setWebsiteIds(
                $websiteIds
            );

            $this->saveVariations($product);
        }

        if (isset($this->productData[$product->getIdFieldName()])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
        }

        // dd($product->getData());

        // $this->debug("Consumer: product->getdata " . print_r($product->getData(),true));

        try {
            $originalSku = $product->getSku();
            $product->setSellerId($this->sellerId);

            if ($product->getData('pre_order_date')) {
                $value = date('Y-m-d', strtotime(str_replace('/', '-', $product->getData('pre_order_date'))));
                $product->setData('pre_order_date', $value);
            }

            $this->debug("Consumer: SAVEDDDD ");

            $product->save();
        } catch(\Exception $e) {
            dd($e->getMessage());
        }

        $this->categoryLinkManagement->assignProductToCategories(
            $product->getSku(),
            $product->getCategoryIds()
        );

        /* $this->handleImageRemoveError($data, $product->getId());*/
        $productId = $product->getId();

        /**
         * Do copying data to marketplace
         */
        if ($productId) {
            $model = $this->sellerProductFactory->create();
            foreach ($model->getCollection()->getData() as $_seller) {
                if ($productId == $_seller['product_id']) {
                    $model->load($_seller['entity_id']);
                }
            }

            if (!$this->request->getParam('is_duplicate')) 
            {
                $model->setProductId($productId)
                    ->setStoreId($this->helper->getCurrentStoreId())
                    ->setSellerId($product->getSellerId())
                    ->setStatus($product->getApproval())
                    ->save();
            }
        }

        // dd($product->getData());

        $isPublish = $this->request->getParam('is_publish');
        if ($isPublish) {
            $configurableMatrixArr = [];

            $configurableMatrixSerialized = $this->request->getParam('configurable-matrix-serialized');
            if ($configurableMatrixSerialized) {
                $configurableMatrixArr = json_decode($configurableMatrixSerialized,true);
            }

            if ($configurableMatrixArr) {
                $typeInstance = $product->getTypeInstance();
                $_children = $typeInstance->getUsedProducts($product);
                if (count($_children) && $configurableMatrixArr) {
                    foreach($_children as $childProduct) {
                        $this->publishVariant($childProduct, $configurableMatrixArr);
                    }
                }
            }

            return $this->saveAndPublish($product);
        }

        if (!$this->request->getParam('is_publish') && !$this->request->getParam('isAjax') && !$this->request->getParam('is_preview')) {
            // $countSavedProducts = $countSavedProducts + 1;
        }

        if ($product->getSku() != $originalSku) {
            $this->messageManager->addNoticeMessage(
                __(
                    'SKU for product %1 has been changed to %2.',
                    $this->escaper->escapeHtml($product->getName()),
                    $this->escaper->escapeHtml($product->getSku())
                )
            );
        }

        // $this->_eventManager->dispatch(
        //     'controller_action_catalog_product_save_entity_after',
        //     ['controller' => $this, 'product' => $product, 'productId' => $productId]
        // );

        // if ($this->helper->getConfig('email_settings/enable_send_email')) {
        //     if (!$this->request->getParam('id')) {
        //         $seller = $this->seller->load((int)$this->sellerId);
        //         $data = $product->getData('name');
        //         $data['seller_name'] = $seller->getShopTitle() ? $seller->getShopTitle() : $seller->getName();
        //         $data['email'] = $seller->getEmail();
        //         $data['url'] = $seller->getUrl();
        //         $this->sender->newSellerProduct($data);
        //     }
        // }

        return $product;
    }

    public function populateCustomTableData(
        $product,
        $customTableData
    ) {
        $skipFields = [
            'entity_id',
            'set_id',
            'created_at',
            'updated_at',
            'images'
        ];

        $generalCustomTableAttributes = [
            'number_of_cards',
            'item_number',
            'card_grade',
            'bar_code_no',
            'bar_code',
            'age_level',
            'language_version',
            'card_grader',
            'toys_line',
            'material',
            'condition',
            'card_condition',
            'language',
            'character',
            'release_date',
            'release_year',
            'card_manufacturer'
        ];
        
        $mainAttrCode = $this->customTableMetadata['mainAttrCode'] ?? null;
        $mainSetsAttrCode = $this->customTableMetadata['mainSetsAttrCode'] ?? null;
        $mainSinglesAttrCode = $this->customTableMetadata['mainSinglesAttrCode'] ?? null;
        $setsFieldToSelet = $this->customTableMetadata['setsFieldToSelet'] ?? [];
        
        $setsTableName = $this->customTableMetadata['setsTableName'] ?? null;
        $sealedProductsTableName = $this->customTableMetadata['sealedProductsTableName'] ?? [];
        $singlesTableName = $this->customTableMetadata['singlesTableName'] ?? [];

        $tableName = null;
        $joinToSetsTable = null;
        $joinField = null;
        
        // get singles data
        if ($this->customTableType == "singles") {
            $tableName = $singlesTableName;

            $joinToSetsTable = true;
            $joinField = 'main_table.set_id=sets.entity_id';
        }

        // get sealed products data
        if ($this->customTableType == "sealedproducts") {
            $tableName = $sealedProductsTableName;
            
            $joinToSetsTable = true;
            if ($this->setsDataArr) {
                $joinField = 'main_table.set_id=sets.entity_id';
            } else {
                $joinField = 'main_table.card_release=sets.card_release';
            }
        }

        // get sets data
        if ($this->customTableType == "sets") {
            $tableName = $setsTableName;
        }

        if (!$tableName) {
            $this->messageManager->addErrorMessage(__('Table name not found.'));
            return null;
        }

        $collection = $this->mainCategoriesConfigProvider->getCustomTableCollection($tableName);
        if (!$collection) {
            $this->messageManager->addErrorMessage(__('Collection not found.'));
            return null;
        }

        // dd($joinField);

        if ($joinToSetsTable) {
            $collection->getSelect()->joinLeft(
                ['sets' => $setsTableName],
                $joinField,
                []
            )->group('main_table.entity_id');
        }

        // dd($collection->getSelect()->__toString());
        
        $customTableDataId = $customTableData['entity_id'] ?? null;
        // dd($customTableDataId);
        if ($customTableDataId) {
            $collection->addFieldToFilter('entity_id', $customTableDataId);
        }

        if (!$collection->getSize()) {
            $this->messageManager->addErrorMessage(__('Data not found.'));
            return null;
        }
        // Get prefix
        $mainAttrId = $this->productData[$mainAttrCode] ?? null;
        // dd($this->productData);
        // dd($mainAttrCode);
        // dd($mainAttrId);
        if ($mainAttrId) {
            $this->prefix = $this->catalogHelper->getPrefixByCategoryId(
                $this->categoryId, 
                null, 
                $mainAttrId, 
                $mainAttrCode
            );
        }

        if (!$this->prefix) {
            $this->messageManager->addErrorMessage(__('Prefix not found.'));
            return null;
        }

        $data = $collection->getFirstItem()->getData();
        if ($data) {
            $singlesName = '';
            $setsName = '';
                
            foreach($data as $field => $value) {
                if ($value && $field != $mainAttrCode && $skipFields && !in_array($field, $skipFields)){
                    $attrCode = $this->prefix . '_' . $field;

                    // Handle field without prefix
                    if (in_array($field, $generalCustomTableAttributes)) {
                        $attrCode = $field;
                    }

                    // Get option id
                    $processedValue = '';

                    try {
                        $attribute = $this->mappingHelper->getAttribute($attrCode);
                        if ($attribute) {
                            $frontendInput = $attribute['frontend_input'] ?? null;
                            if ($frontendInput != 'select' && $frontendInput != 'multiselect') {
                                if ($frontendInput == "date") {
                                    $value = date('Y-m-d', strtotime(str_replace('/', '-', $value)));
                                }
                                $processedValue = $value ?? '';
                            } else {
                                if ($frontendInput == "multiselect") {
                                    $processedValueArr = [];

                                    $valueArr = explode(",", str_replace(", ", ",", $value));
                                    if ($valueArr) {
                                        foreach($valueArr as $val) {
                                            $processedValueArr[] = $this->mappingHelper->getAttributeOptionId($attrCode, $val);
                                        }
                                    }

                                    $processedValue = $processedValueArr ? implode(",", $processedValueArr) : '';
                                } else {
                                    $processedValue = $this->mappingHelper->getAttributeOptionId($attrCode, $value);
                                }
                            }
                        }
                    } catch(\Exception $e) {
                        dd($e->getMessage());
                    }
                    
                    // Set values to product
                    $product->setData($attrCode, $processedValue);

                    if ($field == $mainSinglesAttrCode) {
                        $singlesName = $value;
                    }

                    if ($field == $mainSetsAttrCode) {
                        $setsName = $value;
                    }
                }
            }

            $productIdentifierArr = [];
            if ($singlesName) {
                $productIdentifierArr[] = strtoupper($this->convertToSlug($singlesName));
            }
            
            // if ($setsName) {
            //     $productIdentifierArr[] = strtoupper($this->convertToSlug($setsName));
            // }

            $productIdentifierArr[] = strtoupper($this->makeid(15));

            $productIdentifier = implode("-", $productIdentifierArr);

            $urlKey = strtolower($productIdentifier);
            if ($urlKey) {
                $product->setData('url_key', $urlKey);
            }

            $productNameArr = [];
            if ($singlesName) {
                $productNameArr[] = $singlesName;
            }

            if ($setsName) {
                $productNameArr[] = $setsName;
            }

            if ($mainAttrVal = $product->getData($mainAttrCode)) {
                $productNameArr[] = $this->mappingHelper->getOptionLabelById($mainAttrCode, $mainAttrVal);
            }

            $productName = implode(" | ", $productNameArr);
            if ($productName) {
                $product->setData('name', $productName);
            }

            if ($productIdentifier) {
                $product->setData('sku', $this->sellerId . '-' . $productIdentifier);
                $product->setData('supplier_sku', $productIdentifier);
            }
        }

        return $product;
    }

    public function getImageFromAllImages($customTableData) 
    {
        $result = [];

        $images = $customTableData['all_images'] ?? null;
        if ($images) {
            $imagesArr = explode(",", $images);
            if ($imagesArr) {
                foreach($imagesArr as $imageName) {
                    $result[] = [
                        'entity_id' => $customTableData['entity_id'] ?? null,
                        'filename' => strtolower(basename($imageName))
                    ];
                }
            }
        }

        return $result;
    }

    public function getCustomTableImages($customTableDataArr) 
    {
        $result = [];

        if (isset($customTableDataArr[0]['entity_id'])) {
            foreach($customTableDataArr as $customTableData) {
                $images = $this->getImageFromAllImages($customTableData);
                if ($images) {
                    $result = array_merge($result, $images);
                }
            }
        } else {
            $customTableData = $customTableDataArr;
            $images = $this->getImageFromAllImages($customTableData);
            if ($images) {
                $result = array_merge($result, $images);
            }
        }

        return $result;
    }

    

    public function setMediaGallery($customTableData) 
    {
        $filteredMediaGallery = [];
        $file = null;

        $entityId = (int)$customTableData['entity_id'] ?? null;

        $media_galleryModified = $this->productData['media_gallery_modified']['images'] ?? [];
        if ($media_galleryModified) {
            $filteredMediaGallery['images'] = $media_galleryModified[$entityId] ?? [];
            // dd($filteredMediaGallery);
            if ($filteredMediaGallery && isset($filteredMediaGallery['images'])) {
                foreach($filteredMediaGallery['images'] as $key => $image) {
                    // dd($image);
                    // if ($image && isset($image['position'])) {
                    //     if ($image['position'] == 1) {
                    if (isset($image['file']) && $image['file']) {
                            $file = $image['file'];
                            break;
                    }
                    //     }
                    // }
                }
            }
        }

        // $product->setData('media_gallery', $filteredMediaGallery);
        // $product->setData('image', $file);
        // $product->setData('small_image', $file);
        // $product->setData('thumbnail', $file);
        // $product->setData('swatch_image', $file);
        // dd($file);
        // dd($media_galleryModified);
        $this->productData['media_gallery'] = $filteredMediaGallery;
        $this->productData['image'] = $file;
        $this->productData['thumbnail'] = $file;
        $this->productData['small_image'] = $file;
        $this->productData['swatch_image'] = $file;

        return $this->productData;
    }

    // publish process
    public function publish($listingCountry, $product) 
    {
        // MOVED
        return null;
    }

    public function publishVariants($productIds) 
    {
        if (isset($publish['variants']) && $publish['variants']) {
            foreach($publish['variants'] as $variantProductId => $variant) {
                $variantProduct = $this->productRepository->getById($variantProductId, false, 0);
                $variantProduct->setWebsiteIds($websiteIds);

                if ($websiteIds) {
                    foreach($websiteIds as $index => $websiteId) {
                        $website = $this->storeManager->getWebsite($websiteId);
                        if ($website) {
                            $stores = $website->getStores();
                            if ($stores) {
                                foreach($stores as $store) {
                                    try {
                                        $this->productAction->updateAttributes([$variantProductId], [
                                            'publish_status'=> 1
                                        ], $store->getId());
                                    } catch (\Exception $e) {
                                        $this->messageManager->addErrorMessage($e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }

                $variantProduct->save();

                $productIds[] = $variantProduct->getId();
            }
        }

        return $productIds;
    }

    public function updateVariantProductPriceByWebsiteId($variantProductId, $websiteId, $newPrice) 
    {
        $stores = $this->storeManager->getWebsite($websiteId)->getStores();
        if ($stores) {
            foreach($stores as $store) {
                try {
                    $this->productAction->updateAttributes([$variantProductId], ['price'=> $newPrice], $store->getId());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        return true;
    }

    public function updateProductPriceByWebsiteId($productId, $websiteId, $newPrice) 
    {
        $stores = $this->storeManager->getWebsite($websiteId)->getStores();
        if ($stores) {
            foreach($stores as $store) {
                try {
                    $this->productAction->updateAttributes([$productId], ['price'=> $newPrice], $store->getId());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        return true;
    }

    public function duplicate(int $productId): \Magento\Catalog\Api\Data\ProductInterface
    {
        $product = $this->repository->getById($productId);
        return $this->copier->copy($product);
    }

    public function publishVariant($product, $configurableMatrixArr) 
    {
        $configurableMatrixData = array_filter($configurableMatrixArr, function ($item) use ($product) {
            return $item['id'] == $product->getId();
        });

        // Ubah ke indexed array jika perlu ambil elemen pertama
        $configurableMatrixData = array_values($configurableMatrixData); // Optional
        $configurableMatrixData = $configurableMatrixData[0] ?? [];

        if ($configurableMatrixData) {
            $newWebsiteIds = [];
            $newWebsiteStores = null;

            $publishStoreId = $this->request->getParam('store');
            if ($publishStoreId) {
                $publishStore = $this->storeManager->getStore($publishStoreId);
                if ($publishStore) {
                    $website = $publishStore->getWebsite();
                    if ($website) {
                        $stores = $website->getStores();
                        if ($stores) {
                            $newWebsiteStores = $stores;
                        }
                        
                        $newWebsiteIds[] = $website->getId();
                    }
                }
            }

            $currentWebsiteIds = $product->getWebsiteIds();
            $websiteIds = array_merge($currentWebsiteIds, $newWebsiteIds);
            $websiteIds = array_unique($websiteIds);

            $product->setWebsiteIds($websiteIds);

            $qty = $configurableMatrixData['qty'] ?? 0;
            $visibility = $configurableMatrixData['visibility'] ?? 1;

            $quantity_and_stock_status = [
                'is_in_stock' => $qty > 0 ? 1 : 0,
                'qty' => $qty
            ];

            $product->setData('quantity_and_stock_status', $quantity_and_stock_status);
            // $product->setData('visibility', $visibility);
            $product->save();

            $productId = $product->getId();

            try {
                $this->productAction->updateAttributes([$productId], ['visibility'=> 1], 0);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            if ($newWebsiteStores) {
                $newPrice = $configurableMatrixData['price'] ?? 0;
                foreach($newWebsiteStores as $store) {
                    try {
                        $this->productAction->updateAttributes([$productId], [
                            'publish_status'=> 1
                        ], $store->getId());
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }                                

                    try {
                        $this->productAction->updateAttributes([$productId], ['price'=> $newPrice], $store->getId());
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }

                    try {
                        $this->productAction->updateAttributes([$productId], ['visibility'=> $visibility], $store->getId());
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                }

                $this->helper->removeCache('product_publish_data_cache' . $productId);
                $this->helper->removeCache('product_publish_status_cache' . $productId . '_' . $website->getId());

                 
                $this->flushCacheByIds->execute([$productId]);
            }
        }

        return true;
    }

    

    

    public function redirectToMyProducts() 
    {
        $this->productData = $this->request->getPost('product', []);

        // get type id
        $typeId = isset($this->productData['configurable_attributes_data']) 
        && $this->productData['configurable_attributes_data'] ? 'configurable' : 'simple';
        
        // get category id
        $categoryId = $this->productData['category_ids'] ?? null;

        // get my products url
        $myProductsUrl = $this->catalogHelper->getMyProductsUrlByCategoryId($categoryId, $this->productData, $typeId);

        // Clear my products listing cache
        $this->catalogHelper->clearMyProductsCache();

        // Redirect to my products
        return $myProductsUrl;
    }

    public function addToRabbitmqQueues() 
    {
        $data = $this->request->getPostValue();
        $sellerId = $this->helper->getSellerId();

        // Build Product Payload 
        $productId = (int)$this->request->getParam('id');
        $attributeSetId = (int)$this->request->getParam('set');
        $typeId = $this->request->getParam('type');
        $isPublish = $this->request->getParam('is_publish');
        $isDuplicate = $this->request->getParam('is_duplicate');
        $configurableMatrixSerialized = $this->request->getParam('configurable-matrix-serialized') ?? $this->request->getParam('configurable_matrix_serialized');
        $isAjax = $this->request->getParam('isAjax');
        $isPreview = $this->request->getParam('is_preview');
        $configurableAttributesData = $this->request->getParam('configurable_attributes_data');
        $categoryId = $this->request->getParam('product')['category_ids'] ?? null;

        $request = [
            'id' => $productId,
            'store' => $this->storeId,
            'set' => $attributeSetId,
            'type' => $typeId,
            'is_publish' => $isPublish,
            'is_duplicate' => $isDuplicate,
            'configurable_matrix_serialized' => $configurableMatrixSerialized,
            'is_ajax' => $isAjax,
            'is_preview' => $isPreview,
            'configurable_attributes_data' => $configurableAttributesData,
            'category_id' => $categoryId,
            'new-variations-attribute-set-id' => $attributeSetId,
            'affect_configurable_product_attributes' => $this->request->getParam('affect_configurable_product_attributes'),
            'associated_product_ids_serialized' => $this->request->getParam('associated_product_ids_serialized', '[]')
        ];
        
        $this->debug('Save: Seller ID ' . $sellerId);
        $this->debug('Save: Store ID ' . $this->storeId);
        $this->debug('Save: Build Payload ' . print_r($request, true));
        
        $this->productData = $this->request->getParam('product');

        $payload = [];

        $this->singlesDataArr = $this->productData['sets_data'] ?? [];
        if ($this->singlesDataArr) {
            // Card Game
            if (isset($this->singlesDataArr['card_game'])) {
                $mainAttrCode = 'card_game';

                $payload = array_intersect_key(
                    $this->singlesDataArr, array_flip([
                        $mainAttrCode,
                        'language_version',
                        'language',
                        'card_set',
                        'card_set_abbreviation',
                        'release_year'
                    ])
                );

                $payload[$mainAttrCode] = $this->mappingHelper->getOptionLabelById($mainAttrCode, $this->singlesDataArr[$mainAttrCode]);
            }
        }

        $platformDbData = $this->singlesDataArr && count($this->singlesDataArr) ? $this->singlesDataArr : $this->sealedProductsDataArr;
        // dd($request);
        // Save to queues table
        $queue = $this->rabbitmqQueuesFactory->create();
        $queue->setSellerId($sellerId);
        $queue->setQueueType('product_creation');
        $queue->setCategoryId($categoryId);
        $queue->setPayload(json_encode($payload,true));
        $queue->setStatus('pending');
        $queue->setTotalProducts(count($platformDbData));
        $queue->save();

        // Publish message to RabbitMQ
        $this->publisher->publish(
            'lof.marketplace.product_import_db',
            json_encode([
                'data'      => $data,
                'seller_id' => $sellerId,
                'store_id'  => $this->storeId,
                'build_payload' => $request,
                'queue_id' => $queue->getId()
            ])
        );

        if (isset($this->productData['singles_data']) || isset($this->productData['sealedproducts_data'])) {
            $this->messageManager->addSuccessMessage(__("Your product is currently being processed. You will be notified once it’s complete, or you can view its status by clicking the View Product Queue button."));
        }

        return $this->redirectToMyProducts();
    }

    public function debug($message) 
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/importDbConsumer.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($message);
    }

    public function initCustomTableData() 
    {
        $this->categoryId = $this->productData['category_ids'] ?? null;
        $this->setsDataArr = $this->productData['sets_data'] ?? [];
        $this->singlesDataArr = $this->productData['singles_data'] ?? [];
        $this->sealedProductsDataArr = $this->productData['sealedproducts_data'] ?? [];

        unset($this->setsDataArr['images']);
        unset($this->singlesDataArr['images']);
        unset($this->sealedProductsDataArr['images']);
    }

    public function saveAndPublish($product) 
    {
        $newWebsiteIds = [];
        $newWebsiteStores = null;

        $publishStoreId = $this->request->getParam('store');
        if ($publishStoreId) {
            $publishStore = $this->storeManager->getStore($publishStoreId);
            if ($publishStore) {
                $website = $publishStore->getWebsite();
                if ($website) {
                    $stores = $website->getStores();
                    if ($stores) {
                        $newWebsiteStores = $stores;
                    }
                    
                    $newWebsiteIds[] = $website->getId();
                }
            }
        }

        $currentWebsiteIds = $product->getWebsiteIds();
        $websiteIds = array_merge($currentWebsiteIds, $newWebsiteIds);
        $websiteIds = array_unique($websiteIds);

        $product->setWebsiteIds($websiteIds);
        $product->save();

        

        if ($newWebsiteStores) {
            $productId = $product->getId();
            $newPrice = $product->getPrice();
            foreach($newWebsiteStores as $store) {
                try {
                    $this->productAction->updateAttributes([$productId], [
                        'publish_status'=> 1
                    ], $store->getId());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }                                

                try {
                    $this->productAction->updateAttributes([$productId], ['price'=> $newPrice], $store->getId());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->helper->removeCache('product_publish_data_cache' . $productId);
            $this->helper->removeCache('product_publish_status_cache' . $productId . '_' . $website->getId());
            
            $this->flushCacheByIds->execute([$productId]);

            $this->messageManager->addNoticeMessage(__('It may take 1-2 minutes for the product to appear and reflect updates on the storefront.'));
            $this->messageManager->addSuccessMessage(__('Product successfully published to selected countries.'));
        }

        return true;
    }

    public function addSellerNotification($data) 
    {
        $notification = $this->notificationFactory->create();
        $notification->setData($data);

        $this->notificationResource->save($notification);
    }

    public function modifyMediaGallery() 
    {
        $media_gallery = $this->productData['media_gallery']['images'] ?? [];
        if ($media_gallery) {
            $customTableImageNameArr = [];

            if ($this->setsDataArr) {
                $customTableImageNames = $this->getCustomTableImages($this->setsDataArr);
                if ($customTableImageNames) {
                    $customTableImageNameArr = array_merge($customTableImageNameArr, $customTableImageNames);
                }
            }
            
            if ($this->sealedProductsDataArr) {
                $customTableImageNames = $this->getCustomTableImages($this->sealedProductsDataArr);
                if ($customTableImageNames) {
                    $customTableImageNameArr = array_merge($customTableImageNameArr, $customTableImageNames);
                }
            }

            if ($this->singlesDataArr) {
                $customTableImageNames = $this->getCustomTableImages($this->singlesDataArr);
                if ($customTableImageNames) {
                    $customTableImageNameArr = array_merge($customTableImageNameArr, $customTableImageNames);
                }
            }

            $newMediaGallery = [];

            if ($customTableImageNameArr) {
                foreach($media_gallery as $key => $imageData) {
                    $tmpImgName = $imageData['file'] ?? null;
                    if ($tmpImgName) {
                        foreach ($customTableImageNameArr as $item) {
                            $basename = pathinfo($item['filename'], PATHINFO_FILENAME);
                            if (strpos($tmpImgName, $basename) !== false) {
                                $newMediaGallery[$item['entity_id']][$key] = $imageData; 
                                break;
                            }
                        }
                    }
                }
            }
            
            $this->productData['media_gallery_modified']['images'] = $newMediaGallery;
        }
    }

    public function saveVariations($product) 
    {
        $typeInstance = $product->getTypeInstance();
        if ($typeInstance instanceof \Magento\ConfigurableProduct\Model\Product\Type\Configurable) 
        {
            $_children = $typeInstance->getUsedProducts($product);
            if ($_children) {
                foreach ($_children as $_child) {
                    if (!$_child->getWebsiteIds()) {
                        $_child->setWebsiteIds(
                            $websiteIds
                        );
                        $_child->save();
                    }
                }
            }
        }

        return $product;
    }

    public function getRabbitMqQueueById($queueId) 
    {
        $collection = $this->rabbitmqQueuesFactory->create()
        ->getCollection()
        ->addFieldToFilter('entity_id', $queueId);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getCategoryNameById($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            return $category->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {}

        return null;
    }

    public function createEmptyProduct($typeId, $attributeSetId) 
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $product->setData('_edit_mode', true);

        if ($typeId !== null) {
            $product->setTypeId($typeId);
        }

        if ($this->storeId !== null) {
            $product->setStoreId($this->storeId);
        }

        if ($attributeSetId) {
            $product->setAttributeSetId($attributeSetId);
        }

        return $product;
    }

    public function build()
    {
        $request = $this->request;

        // dd($request->getParams());
        $this->debug("Consumer:build  id " . print_r($request->getParam('id'),true));
        $this->debug("Consumer:build  set " . print_r($request->getParam('set'),true));
        $this->debug("Consumer:build  type " . print_r($request->getParam('type'),true));
        $this->debug("Consumer:build  this->storeId " . print_r($this->storeId,true));

        $productId = (int)$request->getParam('id');
        $attributeSetId = (int)$request->getParam('set');
        $typeId = $request->getParam('type');

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId, true, $this->storeId);
            } catch (\Exception $e) {
                $product = $this->createEmptyProduct(ProductTypes::DEFAULT_TYPE, $attributeSetId);
            }
        } else {
            $product = $this->createEmptyProduct($typeId, $attributeSetId);
        }
        return $product;
    }

    public function makeId($length) {
        $result = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, $charactersLength - 1)];
        }
        return $result;
    }    

    public function convertToSlug($text) {
        return strtolower(
            preg_replace(
                ['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'],
                ['', '-', ''],
                trim($text)
            )
        );
    }
}