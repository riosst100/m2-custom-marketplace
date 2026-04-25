<?php

namespace TCGCollective\MarketplaceProductImportExport\Model;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\AbstractEntity as ImportAbstractEntity;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\EntityInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\ImportExport\Model\Source\Upload;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use Magento\ImportExport\Model\History;

/**
 * Import model
 *
 * @api
 *
 * @method string getBehavior() getBehavior()
 * @method self setEntity() setEntity(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Import extends \Magento\ImportExport\Model\Import
{
    protected $logger;
    protected $filterBuilder;
    protected $searchCriteriaBuilder;
    protected $searchAssets;
    protected $sellerHelper;
    protected $sellerImagesFactory;
    protected $csvProcessor;
    protected $mappingHelper;
    protected $helper;
    protected $request;
    protected $storeManager;
    protected $websiteCollectionCache;
    protected $fileSystem;
    protected $_frontendUrl;


    public const BEHAVIOR_APPEND = 'append';
    public const BEHAVIOR_ADD_UPDATE = 'add_update';
    public const BEHAVIOR_REPLACE = 'replace';
    public const BEHAVIOR_DELETE = 'delete';
    public const BEHAVIOR_CUSTOM = 'custom';

    /**
     * Import source file.
     */
    public const FIELD_NAME_SOURCE_FILE = 'import_file';

    /**
     * Import image archive.
     */
    public const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';

    /**
     * Import images file directory.
     */
    public const FIELD_NAME_IMG_FILE_DIR = 'import_images_file_dir';

    /**
     * Allowed errors count field name
     */
    public const FIELD_NAME_ALLOWED_ERROR_COUNT = 'allowed_error_count';

    /**
     * Validation startegt field name
     */
    public const FIELD_NAME_VALIDATION_STRATEGY = 'validation_strategy';

    /**
     * Import field separator.
     */
    public const FIELD_FIELD_SEPARATOR = '_import_field_separator';

    /**
     * Import multiple value separator.
     */
    public const FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR = '_import_multiple_value_separator';

    /**
     * Import empty attribute value constant.
     */
    public const FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '_import_empty_attribute_value_constant';

    /**
     * Id of the `importexport_importdata` row after validation.
     */
    public const FIELD_IMPORT_IDS = '_import_ids';

    /**
     * Allow multiple values wrapping in double quotes for additional attributes.
     */
    public const FIELDS_ENCLOSURE = 'fields_enclosure';

    /**
     * default delimiter for several values in one cell as default for FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR
     */
    public const DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR = ',';

    /**
     * Import empty attribute default value
     */
    public const DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '__EMPTY__VALUE__';
    public const DEFAULT_SIZE = 50;
    public const MAX_IMPORT_CHUNKS = 4;
    public const IMPORT_HISTORY_DIR = 'import_history/';
    public const IMPORT_DIR = 'import/';

    /**
     * @var EntityInterface
     */
    protected $_entityAdapter;

    /**
     * @Deprecated Property isn't used
     * @var DataHelper
     */
    protected $_importExportData = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_coreConfig;

    /**
     * @var ConfigInterface
     */
    protected $_importConfig;

    /**
     * @var Factory
     */
    protected $_entityFactory;

    /**
     * @var Data
     */
    protected $_importData;

    /**
     * @var CsvFactory
     */
    protected $_csvFactory;

    /**
     * @Deprecated Property isn't used
     * @var FileTransferFactory
     */
    protected $_httpFactory;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var BehaviorFactory
     */
    protected $_behaviorFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var History
     */
    private $importHistoryModel;

    /**
     * @var DateTime
     */
    private $localeDate;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @Deprecated Property isn't used
     * @var Random
     */
    private $random;

    /**
     * @var Upload
     */
    private $upload;

    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param DataHelper $importExportData
     * @param ScopeConfigInterface $coreConfig
     * @param Import\ConfigInterface $importConfig
     * @param Import\Entity\Factory $entityFactory
     * @param Data $importData
     * @param Export\Adapter\CsvFactory $csvFactory
     * @param FileTransferFactory $httpFactory
     * @param UploaderFactory $uploaderFactory
     * @param Source\Import\Behavior\Factory $behaviorFactory
     * @param IndexerRegistry $indexerRegistry
     * @param History $importHistoryModel
     * @param DateTime $localeDate
     * @param array $data
     * @param ManagerInterface|null $messageManager
     * @param Random|null $random
     * @param Upload|null $upload
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        DataHelper $importExportData,
        ScopeConfigInterface $coreConfig,
        ConfigInterface $importConfig,
        Factory $entityFactory,
        Data $importData,
        CsvFactory $csvFactory,
        FileTransferFactory $httpFactory,
        UploaderFactory $uploaderFactory,
        BehaviorFactory $behaviorFactory,
        IndexerRegistry $indexerRegistry,
        History $importHistoryModel,
        DateTime $localeDate,
        array $data = [],
        ManagerInterface $messageManager = null,
        Random $random = null,
        Upload $upload = null
    ) {
        $this->_importExportData = $importExportData;
        $this->_coreConfig = $coreConfig;
        $this->_importConfig = $importConfig;
        $this->_entityFactory = $entityFactory;
        $this->_importData = $importData;
        $this->_csvFactory = $csvFactory;
        $this->_httpFactory = $httpFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->_behaviorFactory = $behaviorFactory;
        $this->_filesystem = $filesystem;
        $this->importHistoryModel = $importHistoryModel;
        $this->localeDate = $localeDate;
        $this->messageManager = $messageManager ?: ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->random = $random ?: ObjectManager::getInstance()
            ->get(Random::class);
        $this->upload = $upload ?: ObjectManager::getInstance()
            ->get(Upload::class);
        $this->logger = $logger;
        parent::__construct(
            $logger, 
            $filesystem,
            $importExportData,
            $coreConfig,
            $importConfig,
            $entityFactory,
            $importData,
            $csvFactory,
            $httpFactory,
            $uploaderFactory,
            $behaviorFactory,
            $indexerRegistry,
            $importHistoryModel,
            $localeDate,
            $data,
            $messageManager,
            $random,
            $upload
        );        
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

    /**
     * Returns source adapter object.
     *
     * @Deprecated
     * @see \Magento\ImportExport\Model\Import\Source\Factory::create()
     * @param string $sourceFile Full path to source file
     * @return AbstractSource
     * @throws FileSystemException
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return Adapter::findAdapterFor(
            $sourceFile,
            $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT),
            $this->getData(self::FIELD_FIELD_SEPARATOR)
        );
    }

    /**
     * Return operation result messages
     *
     * @param ProcessingErrorAggregatorInterface $validationResult
     * @return string[]
     * @throws LocalizedException
     */
    public function getOperationResultMessages(ProcessingErrorAggregatorInterface $validationResult)
    {
        $messages = [];
        if ($this->getProcessedRowsCount()) {
            if ($validationResult->isErrorLimitExceeded()) {
                $messages[] = __('Data validation failed. Please fix the following errors and upload the file again.');

                // errors info
                foreach ($validationResult->getRowsGroupedByErrorCode() as $errorMessage => $rows) {
                    $error = $errorMessage . ' ' . __('in row(s)') . ': ' . implode(', ', $rows);
                    $messages[] = $error;
                }
            } else {
                if ($this->isImportAllowed()) {
                    $messages[] = __('The validation is complete.');
                } else {
                    $messages[] = __('The file is valid, but we can\'t import it for some reason.');
                }
            }

            $messages[] = __(
                'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                $this->getProcessedRowsCount(),
                $this->getProcessedEntitiesCount(),
                $validationResult->getInvalidRowsCount(),
                $validationResult->getErrorsCount(
                    [
                        ProcessingError::ERROR_LEVEL_CRITICAL,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    ]
                )
            );
        } else {
            $messages[] = __('This file does not contain any data.');
        }
        return $messages;
    }

    /**
     * Get attribute type for upcoming validation.
     *
     * @param AbstractAttribute|Attribute $attribute
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getAttributeType(AbstractAttribute $attribute)
    {
        $frontendInput = $attribute->getFrontendInput();
        if ($attribute->usesSource() && in_array($frontendInput, ['select', 'multiselect', 'boolean'])) {
            return $frontendInput;
        } elseif ($attribute->isStatic()) {
            return $frontendInput == 'date' ? 'datetime' : 'varchar';
        } else {
            return $attribute->getBackendType();
        }
    }

    /**
     * DB data source model getter.
     *
     * @return Data
     */
    public function getDataSourceModel()
    {
        return $this->_importData;
    }

    /**
     * Default import behavior getter.
     *
     * @static
     * @return string
     */
    public static function getDefaultBehavior()
    {
        return self::BEHAVIOR_APPEND;
    }

    /**
     * Override standard entity getter.
     *
     * @throws LocalizedException
     * @return string
     */
    public function getEntity()   {
        $entities = $this->_importConfig->getEntities();

        if (empty($this->_data['entity'])
            || !empty($this->_data['entity']) && !isset($entities[$this->_data['entity']])
        ) {
            throw new LocalizedException(__('Entity is unknown'));
        }

        return $this->_data['entity'];
    }

    /**
     * Returns number of checked entities.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_getEntityAdapter()->getProcessedEntitiesCount();
    }

    /**
     * Returns number of checked rows.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getProcessedRowsCount()
    {
        return $this->_getEntityAdapter()->getProcessedRowsCount();
    }

    /**
     * Import/Export working directory (source files, result files, lock files etc.).
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->_varDirectory->getAbsolutePath('importexport/');
    }

    /**
     * Import source file structure to DB.
     *
     * @return bool
     * @throws LocalizedException
     */
    public function importSource()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('Import Source Function');
        $ids = $this->_getEntityAdapter()->getIds();
        if (empty($ids)) {
            // $logger->info('Import Source: No IDs from getIds');
            $idsFromPostData = $this->getData(self::FIELD_IMPORT_IDS);
            if (null !== $idsFromPostData && '' !== $idsFromPostData) {
                $ids = explode(",", $idsFromPostData);
                $this->_getEntityAdapter()->setIds($ids);
            }
        }
        // $logger->info('Import Source: IDs to process: ' . json_encode($ids));
        $this->setData('entity', $this->getDataSourceModel()->getEntityTypeCode($ids));
        // $logger->info('Import Source: Entity to process: ' . $this->getDataSourceModel()->getEntityTypeCode($ids));
        $this->setData('behavior', $this->getDataSourceModel()->getBehavior($ids));

        //Validating images temporary directory path if the constraint has been provided
        if ($this->hasData('images_base_directory')
            && $this->getData('images_base_directory') instanceof Filesystem\Directory\ReadInterface
        ) {
            /** @var Filesystem\Directory\ReadInterface $imagesDirectory */
            $imagesDirectory = $this->getData('images_base_directory');
            if (!$imagesDirectory->isReadable()) {
                $rootWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
                $rootWrite->create($imagesDirectory->getAbsolutePath());
            }
            try {
                $this->setData(
                    self::FIELD_NAME_IMG_FILE_DIR,
                    $imagesDirectory->getAbsolutePath($this->getData(self::FIELD_NAME_IMG_FILE_DIR))
                );
                $this->_getEntityAdapter()->setParameters($this->getData());
            } catch (ValidatorException $exception) {
                throw new LocalizedException(__('Images file directory is outside required directory'), $exception);
            }
        }

        $this->importHistoryModel->updateReport($this);
        $this->addLogComment(__('Begin import of "%1" with "%2" behavior', $this->getEntity(), $this->getBehavior()));

        try {
            // $logger->info('Import Source: Process Import started');
            $result = $this->processImport();
            // $logger->info('Import Source: Process Import finished');
        } catch(\Exception $e) {
            $logger->info('Import Source: Process Import Exception: ' . $e->getMessage());
            $logger->info($e->getTraceAsString());
            $this->importHistoryModel->invalidateReport($this);
        }



        if ($result) {
            $this->addLogComment(
                [
                    __(
                        'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                        $this->getProcessedRowsCount(),
                        $this->getProcessedEntitiesCount(),
                        $this->getErrorAggregator()->getInvalidRowsCount(),
                        $this->getErrorAggregator()->getErrorsCount()
                    ),
                    __('The import was successful.'),
                ]
            );
            $this->importHistoryModel->updateReport($this, true);
        } else {
            $this->importHistoryModel->invalidateReport($this);
        }

        $this->getDataSourceModel()->markProcessedBunches($ids);

        return $result;
    }

    /**
     * Process import.
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function processImport()
    {
        return $this->_getEntityAdapter()->importData();
    }
    
    /**
     * Import possibility getter.
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isImportAllowed()
    {
        return $this->_getEntityAdapter()->isImportAllowed();
    }

    /**
     * Get error aggregator instance.
     *
     * @return ProcessingErrorAggregatorInterface
     * @throws LocalizedException
     */
    public function getErrorAggregator()
    {
        return $this->_getEntityAdapter()->getErrorAggregator();
    }

    public function getImageAssetByName($name, $nameWithExt) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->filterBuilder = $objectManager->get('\Magento\Framework\Api\FilterBuilder');
        $this->searchCriteriaBuilder = $objectManager->get('\Magento\Framework\Api\SearchCriteriaBuilder');
        $this->searchAssets = $objectManager->get('\Magento\MediaGallery\Model\SearchAssets');

        $filters[] = $this->filterBuilder
        ->setField('title')
        ->setConditionType('eq')
        ->setValue($name)
        ->create();

        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $result = $this->searchAssets->execute($searchCriteria);

        return count($result) ? $result[0] : $this->getSellerImages($nameWithExt);
    }

    public function getSellerImages($nameWithExt) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->sellerImagesFactory = $objectManager->create(\TCGCollective\MarketplaceProductImportExport\Model\SellerImagesFactory::class);
        // if ($nameWithExt == "seller-images-upload-tone-14.jpeg") {
        //     dd($nameWithExt);
        //     }
        $collection = $this->sellerImagesFactory->create()
        ->getCollection();
        $collection->addFieldToFilter('title', $nameWithExt);
        $collection->addFieldToFilter('seller_id', $this->sellerHelper->getSellerId());
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getFullImagePath($imageNameWithExt) 
    {
        if ($imageNameWithExt) {
            $imageNameWithExtArr = explode(".", $imageNameWithExt);
            $imageName = $imageNameWithExtArr[0];
            if ($imageName) {
                $image = $this->getImageAssetByName($imageName, $imageNameWithExt);
                if ($image) {
                    return $image->getPath();
                }
            }
        }

        return '';
    }

    /**
     * Move uploaded file.
     *
     * @throws LocalizedException
     * @return string Source file path
     */
    public function uploadSource()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('Upload Source Function');
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->csvProcessor = $objectManager->get('\Magento\Framework\File\Csv');
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        $this->websiteCollectionCache = $objectManager->create(\TCGCollective\MarketPlace\Model\WebsiteCollectionCache::class);

        $actionName = $this->request->getActionName();
        
        $entity = $this->getEntity();
        $extension = 'csv';
        
        $sourceFile = $this->getWorkingDir() . $entity . '.' . $extension;
        $sellerId = $this->helper->getSellerId();

        if ($actionName != "processimport") {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2); 
            foreach ($backtrace as $index => $trace) {
                $file = isset($trace['file']) ? $trace['file'] : 'N/A';
                $line = isset($trace['line']) ? $trace['line'] : 'N/A';
                $function = isset($trace['function']) ? $trace['function'] : 'N/A';
                $class = isset($trace['class']) ? $trace['class'] : '';
                $type = isset($trace['type']) ? $trace['type'] : '';
                
                // $logger->info("#{$index}: {$class}{$type}{$function}() in {$file}:{$line}");
            }
            
            $result = $this->upload->uploadSource($entity);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $extension = pathinfo($result['file'], PATHINFO_EXTENSION);
            $sourceFile = $this->getWorkingDir() . $entity . '.' . $extension;
            $copySourceFile = $this->getWorkingDir() . $entity . '_original.' . $extension;
            // $logger->info('Source File: ' . $sourceFile);
            if (file_exists($sourceFile)) {
                try {
                    $data = $this->csvProcessor->getData($sourceFile);
                    $data[0][] = 'thumbnail_image';
                    $data[0][] = 'small_image';
                    $data[0][] = 'sku';
                    $data[0][] = 'product_type';
                    $data[0][] = 'visibility';
                    $data[0][] = 'seller_id';
                    $data[0][] = 'approval';
                    $data[0][] = 'is_in_stock';
                    $data[0][] = 'url_key';
                    $data[0][] = 'website_code';
                    $data[0][] = '_product_websites';
                    $data[0][] = 'website_id';
                    $data[0][] = 'store_view_code';
                    $data[0][] = '_attribute_set';

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

                    // $paramData = $this->request->getParams();
                    // if ($paramData) {
                    //     foreach($paramData as $field => $param) {
                    //         if (!in_array($field, $unusedParams)) {
                    //             $columnIndex = array_search($field, $data[0]);
                    //             if ($columnIndex === false) {
                    //                 $data[0][] = $field;
                    //             }

                    //             if ($field == "categories") {
                    //                 $categoryId = $param;
                    //             }
                    //         }
                    //     }
                    // }

                    // Fix column name
                    $newDataHeaders = [];

                    $headers = $data[0] ?? [];
                    if ($headers) {
                        foreach($headers as $header) {
                            $columnName = strtolower($header);
                            $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $columnName));
                            $newDataHeaders[] = $columnName;
                        }
                    }

                    $data[0] = $newDataHeaders;

                    $categoriesIndex = array_search("categories", $data[0]);

                    $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $data[1][$categoriesIndex]);

                    $mainAttrCode = $customTableMetadata['mainAttrCode'];

                    $mainAttrCodeIndex = array_search($mainAttrCode, $data[0]);

                    $mainAttrLabel = $data[1][$mainAttrCodeIndex] ?? '';

                    $categoryId = $this->mappingHelper->getCategoryIdByName($data[1][$categoriesIndex]);
                    $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, $mainAttrLabel);

                    $mainAttrCode = $customTableMetadata['mainAttrCode'];
                    $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'];
                    $mainSetsNumberAttrCode = $customTableMetadata['mainSetsNumberAttrCode'];
                    $mainSinglesAttrCode = $customTableMetadata['mainSinglesAttrCode'];
                    $mainSinglesNumberAttrCode = $customTableMetadata['mainSinglesNumberAttrCode'];
                    $singlesCollection = $customTableMetadata['singlesCollection'];
                    $setsCollection = $customTableMetadata['setsCollection'];
                    $setsTableName = $customTableMetadata['setsTableName'];
                    $setsFieldToSelet = $customTableMetadata['setsFieldToSelet'];

                    $mainSetsAttrCode = $prefix . $customTableMetadata['mainSetsAttrCode'];
                    $mainSetsNumberAttrCode = $prefix . $customTableMetadata['mainSetsNumberAttrCode'];
                    $mainSinglesAttrCode = $prefix . $customTableMetadata['mainSinglesAttrCode'];
                    $mainSinglesNumberAttrCode = $prefix . $customTableMetadata['mainSinglesNumberAttrCode'];

                    $mainSetsAttrCodeIndex = array_search($mainSetsAttrCode, $data[0]);
                    $mainSetsNumberAttrCodeIndex = array_search($mainSetsNumberAttrCode, $data[0]);
                    $mainSinglesAttrCodeIndex = array_search($mainSinglesAttrCode, $data[0]);
                    $mainSinglesNumberAttrCodeIndex = array_search($mainSinglesNumberAttrCode, $data[0]);

                    $attributeSetIndex = array_search("_attribute_set", $data[0]);
                    $skuIndex = array_search("sku", $data[0]);
                    $sellerSkuIndex = array_search("supplier_sku", $data[0]);

                    $productTypeIndex = array_search("product_type", $data[0]);
                    $visibilityIndex = array_search("visibility", $data[0]);
                    $sellerIdIndex = array_search("seller_id", $data[0]);
                    $approvalIndex = array_search("approval", $data[0]);
                    $qtyIndex = array_search("qty", $data[0]);

                    $isInStockIndex = array_search("is_in_stock", $data[0]);
                    $urlKeyIndex = array_search("url_key", $data[0]);

                    $publishStatusIndex = array_search("publish_status", $data[0]);
                    $listingCountryIndex = array_search("listing_country", $data[0]);
                    $websiteIndex = array_search("website_code", $data[0]);
                    $productWebsiteIndex = array_search("_product_websites", $data[0]);
                    $storeViewCodeIndex = array_search("store_view_code", $data[0]);
                    $websiteIdIndex = array_search("website_id", $data[0]);
                    
                    $imageIndex = array_search("base_image", $data[0]);
                    $thumbnailIndex = array_search("thumbnail_image", $data[0]);
                    $mediaImageIndex = array_search("additional_images", $data[0]);
                    $smallImageIndex = array_search("small_image", $data[0]);
                    

                    

                    $defaultWebsite = $this->storeManager->getStore()->getWebsite();
                    
                    $newRowData = [];
                    $newRowData[] = $data[0];
                    foreach ($data as $key => $_data) 
                    {
                        if (!$key) {
                            continue;
                        }

                        // LISTING DATA
                        $listingWebsiteId = null;
                        $listingWebsiteCode = null;
                        $defaultGroupId = null;
                        $listingWebsite = null;

                        $publishStatus = $_data[$publishStatusIndex] ?? 0;
                        $listingCountry = $_data[$listingCountryIndex] ?? 0;

                        

                        $_data[$publishStatusIndex] = $publishStatus;
                        
                        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
                        if ($websiteCollection) {
                            $websites = $websiteCollection['items'] ?? [];
                            if ($websites) {
                                foreach ($websites as $website) {
                                    if ($website['name'] == $listingCountry) {
                                        $listingWebsite = $website;
                                    }
                                }
                            }
                        }

                        if ($listingWebsite) {
                            $listingWebsiteId = $listingWebsite['website_id'];
                            $listingWebsiteCode = $listingWebsite['code'];
                            $defaultGroupId = $listingWebsite['default_group_id'];
                        }
                        
                        // IMAGES
                        if ($listingWebsiteId && $listingWebsiteId == $defaultWebsite->getId()) {

                            // base_image
                            if ($imageIndex != false) {
                                $baseImage = $_data[$imageIndex] ?? null;
                                if ($baseImage) {
                                    $fullPathImage = $this->getFullImagePath($baseImage);
                                    $_data[$imageIndex] = $fullPathImage;
                                    if ($thumbnailIndex != false) {
                                        $_data[$thumbnailIndex] = $fullPathImage;
                                    }
                                    if ($smallImageIndex != false) {
                                        $_data[$smallImageIndex] = $fullPathImage;
                                    }
                                } else {
                                    $_data[$imageIndex] = '';
                                    if ($thumbnailIndex != false) {
                                        $_data[$thumbnailIndex] = '';
                                    }
                                    if ($smallImageIndex != false) {
                                        $_data[$smallImageIndex] = '';
                                    }
                                }
                            }

                            // additional_images
                            if ($mediaImageIndex != false) {
                                $mediaImages = $_data[$mediaImageIndex] ?? null;
                                if ($mediaImages) {
                                    $newFullPathImageArr = [];

                                    $mediaImagesArr = explode(",", $mediaImages);
                                    if ($mediaImagesArr) {
                                        foreach($mediaImagesArr as $mediaImage) {
                                            $newFullPathImageArr[] = $this->getFullImagePath($mediaImage);
                                    
                                        }
                                    }

                                    $_data[$mediaImageIndex] = $newFullPathImageArr ? implode(",", $newFullPathImageArr) : "";
                                } else {
                                    $_data[$mediaImageIndex] = '';
                                }
                            }
                        } else {
                            unset($_data[$imageIndex]);
                            unset($newRowData[0][$imageIndex]);

                            unset($_data[$thumbnailIndex]);
                            unset($newRowData[0][$thumbnailIndex]);

                            unset($_data[$mediaImageIndex]);
                            unset($newRowData[0][$mediaImageIndex]);

                            unset($_data[$smallImageIndex]);
                            unset($newRowData[0][$smallImageIndex]);
                        }

                        // SKU DATA

                        // dd($sellerId.'_'.strtolower($_data[$sellerSkuIndex]));
                        
                        $_data[$skuIndex] = $sellerId.'_'.strtolower($_data[$sellerSkuIndex]);

                        // SETS/SINGLES DATA
                        // if ($listingWebsiteId && $listingWebsiteId != $defaultWebsite->getId()) {
                        //     $setId  = $this->getRequest()->getParam('set_id');
                        //     if ($singlesCollection) {
                        //         $singlesCollection->addFieldToFilter('set_id', $setId);
                        //         $singlesCollection->getSelect()->joinLeft(
                        //             ['sets' => $setsTableName],
                        //             'main_table.set_id=sets.entity_id',
                        //             $setsFieldToSelet
                        //         );
                                
                        //         if ($singlesCollection->getSize()) {
                        //             foreach($singlesCollection as $index => $singlesData) {
                        //                 unset($_data[$mainSetsAttrCodeIndex]);
                        //                 unset($newRowData[0][$mainSetsAttrCodeIndex]);
                        //             }
                        //         }
                        //     }
                        // }
                        
                        // REQUIRED DATA
                        $_data[$productTypeIndex] = 'simple';
                        $_data[$visibilityIndex] = 'Catalog, Search';
                        $_data[$sellerIdIndex] = $sellerId;
                        $_data[$approvalIndex] = 'Approved';

                        // STOCK DATA
                        if ($listingWebsiteId && $listingWebsiteId == $defaultWebsite->getId()) {
                            $isInStock = 0;
                            
                            $qty = $_data[$qtyIndex] ?? 0;
                            if ($qty) {
                                $isInStock = 1;
                            }

                            $_data[$isInStockIndex] = $isInStock;
                        } else {
                            unset($_data[$qtyIndex]);
                            unset($newRowData[0][$qtyIndex]);

                            unset($_data[$isInStockIndex]);
                            unset($newRowData[0][$isInStockIndex]);
                        }

                        // URL KEY
                        // if ($listingWebsiteId && $listingWebsiteId == $defaultWebsite->getId()) {
                            $productIdentifierArr = [];
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
                            $productIdentifierArr[] = strtoupper($this->makeid(10));
                            $productIdentifier = implode("-", $productIdentifierArr);

                            $urlKey = strtolower($productIdentifier);
                            if ($urlKey) {
                                $_data[$urlKeyIndex] = $urlKey;
                            }
                        // } else {
                        //     unset($_data[$urlKeyIndex]);
                        //     unset($newRowData[0][$urlKeyIndex]);
                        // }

                        // WEBSITE DATA
                        $_data[$websiteIndex] = $listingWebsiteCode;
                        $_data[$productWebsiteIndex] = $listingWebsiteCode;
                        $_data[$websiteIdIndex] = $listingWebsiteId;

                        // PARAMS DATA
                        // if ($paramData) {
                        //     foreach($paramData as $field => $param) {
                        //         if (!in_array($field, $unusedParams)) {
                        //             $columnIndex = array_search($field, $data[0]);
                        //             if ($columnIndex) {
                        //                 if ($listingWebsiteId && $listingWebsiteId == $defaultWebsite->getId()) {
                        //                     if ($field == "_attribute_set") {
                        //                         $_data[$columnIndex] = $param;
                        //                     } elseif ($field == "categories") {
                        //                         $category = $this->mappingHelper->getCategoryById($param);
                        //                         if ($category) {
                        //                             $categoryPathIds = $category->getPathIds();
                        //                             if ($categoryPathIds) {
                        //                                 $categoryPathName = $this->mappingHelper->getCategoryPathLabelByIds($categoryPathIds);
                        //                                 if ($categoryPathName) {
                        //                                     $_data[$columnIndex] = $categoryPathName;
                        //                                 }
                        //                             }
                        //                         }
                        //                     } else {
                        //                         $label = $param ? $this->mappingHelper->getOptionLabelById($field, $param) : null;
                        //                         $_data[$columnIndex] = $label ?? "";
                        //                     }
                        //                 } else {
                        //                     unset($_data[$columnIndex]);
                        //                     unset($newRowData[0][$columnIndex]);
                        //                 }
                        //             }
                        //         }
                        //     }
                        // }

                        // STORE CODE DATA
                        if ($listingWebsiteId != $this->storeManager->getStore()->getWebsite()->getId()) {
                            $website = $this->storeManager->getWebsite($listingWebsiteId);
                            // dd($website->getData());
                            if ($website) {
                                $stores = $website->getStores();
                                if ($stores) {
                                    foreach($stores as $store) {
                                        if ($store->getId() == $website->getDefaultStore()->getId()) {
                                            $_data[$storeViewCodeIndex] = $store->getCode();
                                        }
                                    }
                                }
                            }
                        } else {
                            unset($_data[$storeViewCodeIndex]);
                            unset($newRowData[0][$storeViewCodeIndex]);
                        }

                        $_data[$attributeSetIndex] = '';

                        // dd($_data);

                        // dd($newRowData[0] . '<br/>' . $_data);
                        // Pastikan jumlah elemen sama
                        // if (count($_data) < count($newRowData[0])) {
                        //     $_data = array_pad($_data, count($newRowData[0]), null);
                        // }

                        // Lalu combine
                        // dd(array_combine($newRowData[0], $_data));
                        // dd(array_combine($newRowData[0], $_data));


                        $attributeSetData = $this->mappingHelper->getAttributeSetDataByCategories(
                            array_combine($newRowData[0], $_data), 
                            $_data[$categoriesIndex], 
                            $customTableMetadata
                        );

                        if ($attributeSetData) {
                            $attributeSetName = $attributeSetData['attribute_set_name'] ?? '';
                            $_data[$attributeSetIndex] = $attributeSetName;
                        }

                        // dd($attributeSetName);

                        if ($categoriesIndex) {
                            $categoryName = $_data[$categoriesIndex] ?? null;
                            $categoryPaths = $this->mappingHelper->getCategoryPathsByName($categoryName);
                            if ($categoryPaths) {
                                // dd($categoryPaths);
                                $_data[$categoriesIndex] = $categoryPaths;
                            } else {
                                $_data[$categoriesIndex] = 2;
                            }
                        }

                        $trimmedData = array_map('trim', $_data);

                        $newRowData[] = $trimmedData;

                        // $logger->info('Data:' . print_r($trimmedData, true));
                    }

                    $this->copyOriginalFile($sourceFile, $copySourceFile);
                    $this->exportProductAsCsv($newRowData, $sourceFile);
                } catch (\Exception $e) {}
            }

            $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);
            $this->_removeBom($sourceFile);
            $this->createHistoryReport($sourceFileRelative, $entity, $extension, $result);
        }
        
        return $sourceFile;
    }

    public function copyOriginalFile($source, $destination) 
    {
        try {
            if (!file_exists($source)) {
                $this->logger->error("Source file does not exist: " . $source);
                return false;
            }

            if (copy($source, $destination)) {
                return true;
            } else {
                $this->logger->error("Failed to copy file from $source to $destination");
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->critical("An error occurred while copying file: " . $e->getMessage());
            return false;
        }
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
        // dd($file);
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

            $handle = fopen($file, 'w');

            // Menambahkan BOM di awal
            fwrite($handle, "\xEF\xBB\xBF");

            // Menulis data produk ke file CSV
            foreach ($productData as $row) {
                fputcsv($handle, $row);
            }

            } catch(\Exception $e) {
                dd($e->getMessage());
            }
            // dd($file);
        }

        return true;
    }

    /**
     * Move uploaded file and provide source instance.
     *
     * @return Import\AbstractSource
     * @throws LocalizedException
     * @since 100.2.7
     */
    public function uploadFileAndGetSource()
    {
        $sourceFile = $this->uploadSource();
        try {
            $source = $this->_getSourceAdapter($sourceFile);
        } catch (\Exception $e) {
            $this->_varDirectory->delete($this->_varDirectory->getRelativePath($sourceFile));
            throw new LocalizedException(__($e->getMessage()));
        }

        return $source;
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
     * Validates source file and returns validation result
     *
     * Before validate data the method requires to initialize error aggregator (ProcessingErrorAggregatorInterface)
     * with 'validation strategy' and 'allowed error count' values to allow using this parameters in validation process.
     *
     * @param AbstractSource $source
     * @return bool
     * @throws LocalizedException
     */
    public function validateSource(AbstractSource $source)
    {
        $this->addLogComment(__('Begin data validation'));

        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->getData(self::FIELD_NAME_VALIDATION_STRATEGY),
            $this->getData(self::FIELD_NAME_ALLOWED_ERROR_COUNT)
        );

        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);            
        
            $adapter = $this->_getEntityAdapter()->setSource($source);
            // $logger->info('Adapter: ' . get_class($adapter));
            $adapter->validateData();
        } catch (\Exception $e) {
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $e->getMessage()
            );
        }

        $messages = $this->getOperationResultMessages($errorAggregator);
        $this->addLogComment($messages);

        $result = !$errorAggregator->isErrorLimitExceeded();
        if ($result) {
            $this->addLogComment(__('Import data validation is complete.'));
        }
        return $result;
    }

    /**
     * Invalidate indexes by process codes.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function invalidateIndex()
    {
        $relatedIndexers = $this->_importConfig->getRelatedIndexers($this->getEntity());
        if (empty($relatedIndexers)) {
            return $this;
        }

        foreach (array_keys($relatedIndexers) as $indexerId) {
            try {
                $indexer = $this->indexerRegistry->get($indexerId);

                if (!$indexer->isScheduled()) {
                    $indexer->invalidate();
                }
                // phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\InvalidArgumentException $e) {
            }
        }

        return $this;
    }

    /**
     * Gets array of entities and appropriate behaviours
     * array(
     *     <entity_code> => array(
     *         'token' => <behavior_class_name>,
     *         'code'  => <behavior_model_code>,
     *     ),
     *     ...
     * )
     *
     * @return array
     * @throws LocalizedException
     */
    public function getEntityBehaviors()
    {
        $behaviourData = [];
        $entities = $this->_importConfig->getEntities();
        foreach ($entities as $entityCode => $entityData) {
            $behaviorClassName = isset($entityData['behaviorModel']) ? $entityData['behaviorModel'] : null;
            if ($behaviorClassName && class_exists($behaviorClassName)) {
                /** @var $behavior AbstractBehavior */
                $behavior = $this->_behaviorFactory->create($behaviorClassName);
                $behaviourData[$entityCode] = [
                    'token' => $behaviorClassName,
                    'code' => $behavior->getCode() . '_behavior',
                    'notes' => $behavior->getNotes($entityCode),
                ];
            } else {
                throw new LocalizedException(
                    __('The behavior token for %1 is invalid.', $entityCode)
                );
            }
        }
        return $behaviourData;
    }

    /**
     * Get array of unique entity behaviors
     * array(
     *     <behavior_model_code> => <behavior_class_name>,
     *     ...
     * )
     *
     * @return array
     * @throws LocalizedException
     */
    public function getUniqueEntityBehaviors()
    {
        $uniqueBehaviors = [];
        $behaviourData = $this->getEntityBehaviors();
        foreach ($behaviourData as $behavior) {
            $behaviorCode = $behavior['code'];
            if (!isset($uniqueBehaviors[$behaviorCode])) {
                $uniqueBehaviors[$behaviorCode] = $behavior['token'];
            }
        }
        return $uniqueBehaviors;
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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);            
        // $logger->info('Create history report called');
        // RIO : MODIFY FILE NAME TO GET ORIGINAL UPLOADED CSV
        $sourceFileRelative = str_replace('catalog_product', 'catalog_product_original', $sourceFileRelative);

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
            $addReport = $this->importHistoryModel->addReport($copyName);
            // $logger->info('History report added: ' . $addReport->getHistoryId());

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->session = $objectManager->create(\Magento\Framework\Session\SessionManager::class);
            $this->session->setData('history_id', $addReport->getHistoryId());
        }
        return $this;
    }

    /**
     * Get count of created items
     *
     * @return int
     * @throws LocalizedException
     */
    public function getCreatedItemsCount()
    {
        return $this->_getEntityAdapter()->getCreatedItemsCount();
    }

    /**
     * Get count of updated items
     *
     * @return int
     * @throws LocalizedException
     */
    public function getUpdatedItemsCount()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);            
        // $logger->info('Updated Items Count called: ' . $this->_getEntityAdapter()->getUpdatedItemsCount());
        // $logger->info('Adapter Methods:' . print_r(get_class_methods($this->_getEntityAdapter()), true));
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10); 
        foreach ($backtrace as $index => $trace) {
            $file = isset($trace['file']) ? $trace['file'] : 'N/A';
            $line = isset($trace['line']) ? $trace['line'] : 'N/A';
            $function = isset($trace['function']) ? $trace['function'] : 'N/A';
            $class = isset($trace['class']) ? $trace['class'] : '';
            $type = isset($trace['type']) ? $trace['type'] : '';
            
            // $logger->info("#{$index}: {$class}{$type}{$function}() in {$file}:{$line}");
        }
        return $this->_getEntityAdapter()->getUpdatedItemsCount();
    }

    /**
     * Get count of deleted items
     *
     * @return int
     * @throws LocalizedException
     */
    public function getDeletedItemsCount()
    {
        return $this->_getEntityAdapter()->getDeletedItemsCount();
    }

    /**
     * Retrieve Ids of Validated Rows
     *
     * @return int[]
     */
    public function getValidatedIds() : array
    {
        return $this->_getEntityAdapter()->getIds() ?? [];
    }
}
