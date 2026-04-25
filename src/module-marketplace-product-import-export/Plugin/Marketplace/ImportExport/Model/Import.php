<?php

namespace TCGCollective\MarketplaceProductImportExport\Plugin\Marketplace\ImportExport\Model;

class Import
{
    protected $csvProcessor;
    protected $mappingHelper;
    protected $request;
    protected $_entityAdapter;
    protected $fileSystem;
    protected $_frontendUrl;


    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Lof\MarketPlace\Model\SellerProduct
     */
    protected $sellerproduct;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * Product constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @param \Lof\MarketPlace\Model\SellerProduct $sellerproduct
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Lof\MarketPlace\Model\SellerProduct $sellerproduct
    ) {
        $this->_productFactory = $productFactory;
        $this->helper = $helper;
        $this->sellerproduct = $sellerproduct;
    }
    
    public function aroundUploadSource(
        \Magento\ImportExport\Model\Import $subject,
        \Closure $proceed
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->csvProcessor = $objectManager->get('\Magento\Framework\File\Csv');
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        $routeName = $this->request->getRouteName();
        $controllerName = $this->request->getControllerName();
        $actionName = $this->request->getActionName();

        
        $entity = $subject->getEntity();
        $extension = 'csv';

        // RIO
        $sourceFile = $subject->getWorkingDir() . $entity . '.' . $extension;
       

        $sellerId = $this->helper->getSellerId();
        if ($actionName != "processimport") {
            
        $result = $this->upload->uploadSource($entity);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);
        $sourceFile = $subject->getWorkingDir() . $entity . '.' . $extension;
        
        if (file_exists($sourceFile)) {
            try {
                $data = $this->csvProcessor->getData($sourceFile);
                

                $data[0][] = 'sku';
                $data[0][] = 'product_type';
                $data[0][] = 'visibility';
                $data[0][] = 'seller_id';
                $data[0][] = 'approval';

                

                

                $categoryId = null;

                

                $unusedParams = [
                    "form_key",
                    "entity",
                    "template_url",
                    "behavior",
                    "validation_strategy",
                    "allowed_error_count",
                    "_import_field_separator",
                    "_import_multiple_value_separator",
                    "import_images_file_dir"
                ];

                $paramData = $this->request->getParams();
                if ($paramData) {
                    foreach($paramData as $field => $param) {
                        if (!in_array($field, $unusedParams)) {
                            
                            $columnIndex = array_search($field, $data[0]);
                            // if ($field == "card_game") {
                            //     dd($columnIndex);
                            // }
                            if ($columnIndex === false) {
                                $data[0][] = $field;
                            }

                            if ($field == "categories") {
                                $categoryId = $param;
                            }
                        }
                    }
                }

                $customTableMetadata = $this->mappingHelper->getCustomTableMetadata($categoryId);
                $mainAttrCode = $customTableMetadata['mainAttrCode'];

                $mainAttrCodeIndex = array_search($mainAttrCode, $data[0]);

                $mainAttrLabel = $data[1][$mainAttrCodeIndex] ?? '';

                $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, $mainAttrLabel);

                $mainSetsAttrCode = $prefix . $customTableMetadata['mainSetsAttrCode'];
                $mainSetsNumberAttrCode = $prefix . $customTableMetadata['mainSetsNumberAttrCode'];
                $mainSinglesAttrCode = $prefix . $customTableMetadata['mainSinglesAttrCode'];
                $mainSinglesNumberAttrCode = $prefix . $customTableMetadata['mainSinglesNumberAttrCode'];

                $mainSetsAttrCodeIndex = array_search($mainSetsAttrCode, $data[0]);
                $mainSetsNumberAttrCodeIndex = array_search($mainSetsNumberAttrCode, $data[0]);
                $mainSinglesAttrCodeIndex = array_search($mainSinglesAttrCode, $data[0]);
                $mainSinglesNumberAttrCodeIndex = array_search($mainSinglesNumberAttrCode, $data[0]);

                $skuIndex = array_search("sku", $data[0]);

                $productTypeIndex = array_search("product_type", $data[0]);
                $visibilityIndex = array_search("visibility", $data[0]);
                $sellerIdIndex = array_search("seller_id", $data[0]);
                $approvalIndex = array_search("approval", $data[0]);

                // dd($data[0]);
                

                $newRowData = [];
                foreach ($data as $key => $_data) {
                    // if (!$key) {
                    //     continue;
                    // }

                    if ($key) {
                        // PARAMS
                        

                        $productIdentifierArr = [];

                        // CARD NAME
                        $cardName = $_data[$mainSinglesAttrCodeIndex] ?? null;
                        if ($cardName) {
                            $cardIdentifierArr = [];
                            $cardNumber = $_data[$mainSinglesNumberAttrCodeIndex] ?? null;
                            if ($cardNumber) {
                                $cardIdentifierArr[] = strtoupper($this->convertToSlug($cardNumber));
                                $cardName = $cardName . ' ' . '#' . $cardNumber;
                            }

                            $productIdentifierArr[] = implode("-", $cardIdentifierArr);
                        }

                        // SETS NAME
                        $setsName = $_data[$mainSetsAttrCodeIndex] ?? null;
                        if ($setsName) {
                            $setsIdentifierArr = [];
                            $setAbbrv = $_data[$mainSetsNumberAttrCodeIndex] ?? null;
                            if ($setAbbrv) {
                                $setsIdentifierArr[] = strtoupper($this->convertToSlug($setAbbrv));
                                $setsName = $setsName . ' (' . $setAbbrv . ')';
                            }

                            $productIdentifierArr[] = implode("-", $setsIdentifierArr);
                        }

                        // SET PRODUCT NAME

                        $productIdentifierArr[] = strtoupper($this->makeid(10));

                        $productIdentifier = implode("-", $productIdentifierArr);
                        if ($productIdentifier) {
                            $_data[$skuIndex] = strtolower($productIdentifier);
                        }

                        $_data[$productTypeIndex] = 'simple';
                        $_data[$visibilityIndex] = 'Catalog, Search';
                        $_data[$sellerIdIndex] = $sellerId;
                        $_data[$approvalIndex] = 'Approved';

                        if ($paramData) {
                            foreach($paramData as $field => $param) {
                                if (!in_array($field, $unusedParams)) {

                                    $columnIndex = array_search($field, $data[0]);
                                    // dd($columnIndex);
                                    if ($columnIndex) {
                                        if ($field == "_attribute_set") {
                                            // dd($columnIndex.$param);
                                            $_data[$columnIndex] = $param;
                                        } elseif ($field == "categories") {
                                            $category = $this->mappingHelper->getCategoryById($param);
                                            if ($category) {
                                                $categoryPathIds = $category->getPathIds();
                                                if ($categoryPathIds) {
                                                    $categoryPathName = $this->mappingHelper->getCategoryPathLabelByIds($categoryPathIds);
                                                    if ($categoryPathName) {
                                                        $_data[$columnIndex] = $categoryPathName;
                                                        // dd($columnIndex.$categoryPathName);
                                                    }
                                                }
                                            }
                                        } else {
                                            $label = $param ? $this->mappingHelper->getOptionLabelById($field, $param) : null;
                                            // if ($label) {
                                                $_data[$columnIndex] = $label ?? "";
                                                // dd($data[0]);
                                            // }
                                        }
                                    }
                                }
                            }

                            // dd($_data);
                        }

                        // dd($_data);
                    }

                    // dd(count($_data));
                    // dd(count($data[0]));
                    
                    $newRowData[] = $_data;
                }

                $this->exportProductAsCsv($newRowData, $sourceFile);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
        // END RIO
        $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);
        $this->_removeBom($sourceFile);
        $this->createHistoryReport($sourceFileRelative, $entity, $extension, $result);
        }

        
        return $sourceFile;
    }

    /**
     * Remove BOM from a file
     *
     * @param string $sourceFile
     * @return $this
     * @throws FileSystemException
     */
    protected function _removeBom($sourceFile)
    {
        $driver = $this->_varDirectory->getDriver();
        $string = $driver->fileGetContents($this->_varDirectory->getAbsolutePath($sourceFile));
        if ($string !== false && substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $string = substr($string, 3);
            $driver->filePutContents($this->_varDirectory->getAbsolutePath($sourceFile), $string);
        }
        return $this;
    }

    /**
     * Create history report
     *
     * @param string $sourceFileRelative
     * @param string $entity
     * @param string $extension
     * @param array $result
     * @return $this
     * @throws LocalizedException
     */
    protected function createHistoryReport($sourceFileRelative, $entity, $extension = null, $result = null)
    {
        if ($this->isReportEntityType($entity)) {
            if (is_array($sourceFileRelative)) {
                $fileName = $sourceFileRelative['file_name'];
                $sourceFileRelative = $this->_varDirectory->getRelativePath(self::IMPORT_DIR . $fileName);
            } elseif (isset($result['name'])) {
                $fileName = $result['name'];
            } elseif ($extension !== null) {
                $fileName = $entity . $extension;
            } else {
                // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
                $fileName = basename($sourceFileRelative);
            }
            $copyName = $this->localeDate->gmtTimestamp() . '_' . $fileName;
            $copyFile = self::IMPORT_HISTORY_DIR . $copyName;
            try {
                if ($this->_varDirectory->isExist($sourceFileRelative)) {
                    $this->_varDirectory->copyFile($sourceFileRelative, $copyFile);
                } else {
                    $content = $this->_varDirectory->getDriver()->fileGetContents($sourceFileRelative);
                    $this->_varDirectory->writeFile($copyFile, $content);
                }
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('Source file coping failed'));
            }
            $this->importHistoryModel->addReport($copyName);
        }
        return $this;
    }

    /**
     * Retrieve processed reports entity types
     *
     * @param string|null $entity
     * @return bool
     * @throws LocalizedException
     */
    public function isReportEntityType($entity = null)
    {
        $result = false;
        if (!$entity) {
            $entity = $this->getEntity();
        }
        if ($entity !== null && $this->_getEntityAdapter()->getEntityTypeCode() != $entity) {
            $entities = $this->_importConfig->getEntities();
            if (isset($entities[$entity])) {
                try {
                    $result = $this->_getEntityAdapter()->isNeedToLogInHistory();
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __('Please enter a correct entity model')
                    );
                }
            } else {
                throw new LocalizedException(__('Please enter a correct entity model'));
            }
        } else {
            $result = $this->_getEntityAdapter()->isNeedToLogInHistory();
        }
        return $result;
    }

    /**
     * Create instance of entity adapter and return it
     *
     * @throws LocalizedException
     * @return EntityInterface
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $entities = $this->_importConfig->getEntities();
            if (isset($entities[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = $this->_entityFactory->create($entities[$this->getEntity()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }
                if (!$this->_entityAdapter instanceof AbstractEntity &&
                    !$this->_entityAdapter instanceof ImportAbstractEntity
                ) {
                    throw new LocalizedException(
                        __(
                            'The entity adapter object must be an instance of %1 or %2.',
                            AbstractEntity::class,
                            ImportAbstractEntity::class
                        )
                    );
                }

                // check for entity codes integrity
                if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                    throw new LocalizedException(
                        __('The input entity code is not equal to entity adapter code.')
                    );
                }
            } else {
                throw new LocalizedException(__('Please enter a correct entity.'));
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
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

    public function exportProductAsCsv($productData, $file) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->csvProcessor = $objectManager->get('\Magento\Framework\File\Csv'); 
        $this->fileSystem = $objectManager->get('\Magento\Framework\Filesystem');
        $this->_frontendUrl = $objectManager->create(\Magento\Framework\UrlInterface::class);

        // $fileName = 'product_template_' . time() . '.csv';

        // $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        // $filePath = $varDirectory->getAbsolutePath($fileName);
        
        // die('<pre>'.print_r($productData,true));
        if ($productData) {
            try {
            $TEST = $this->csvProcessor->saveData($file, $productData);
            } catch(\Exception $e) {
                dd($e->getMessage());
            }
            // dd($file);
        }

        return true;
    }
}
