<?php

namespace TCGCollective\MarketPlace\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class PlatformDatabaseCache
{
    protected $mappingHelper;
    protected $prdAttrLinkCollFactory;
    protected $attributeCollection;


    const CACHE_TAG = 'platform_database_cache'; // Custom cache identifier

    private $cache;
    private $collectionFactory;
    private $attributeSetGroup;

    public function __construct(
        CacheInterface $cache,
        CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Entity\Attribute\Group $attributeSetGroup
    ) {
        $this->cache = $cache;
        $this->collectionFactory = $collectionFactory;
        $this->attributeSetGroup = $attributeSetGroup;
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

    /**
     * Get cached website collection or load and cache it.
     *
     * @return array
     */
    public function getAttributeSetMappingDataByCategoryName($rowData, $categoryName)
    {
        $startTime = microtime(true);

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->prdAttrLinkCollFactory = $objectManager->get(\TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory::class);

        $mappingData = [];

        $attributeSetId = null;
        $processedAttributeCode = [];

        // Get mapping data
        if ($categoryName) {
            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            if ($customTableMetadata) {
                $mainAttrCode = $customTableMetadata['mainAttrCode'];

                $cacheKey = self::CACHE_TAG . '_attribute_set_mapping_data_' . $this->convertToSlug($categoryName) . '_' . $rowData[$mainAttrCode] . '_' . $rowData['language_version'] . '_' . $rowData['card_product_type'];
                $cachedData = $this->cache->load($cacheKey);

                if ($cachedData) {
                    // return unserialize($cachedData);
                }

                $mainAttributeSet = null;

                // GET ATTRIBUTE SET BY MASTER ATTRIBUTE
                if ($mainAttrCode) {
                    $mainAttrVal = $rowData[$mainAttrCode] ?? null;
                    if ($mainAttrVal) {
                        if (is_numeric($mainAttrVal)) {
                            $attrOptionId = $mainAttrVal;
                        } else {
                            $attrOptionId = $this->getOptionId($mainAttrCode, $mainAttrVal);
                        }
                        
                        if ($attrOptionId) {
                            $collection = $this->prdAttrLinkCollFactory->create()
                            ->addFieldToFilter('attribute_code', $mainAttrCode)
                            ->addFieldToFilter('attribute_option_id', $attrOptionId)
                            ->addFieldToFilter('mapping_type', 'attribute_set');

                            $collection->setOrder('priority', 'ASC');

                            if ($collection->getSize()) {
                                $mappingModel = $collection->getFirstItem();

                                $mappingData = [];
                                $mappingData['attribute_set_id'] = $mappingModel->getTargetAttributeSetId();
                                $mappingData['mapping_group_id'] = $mappingModel->getGroupId();
                                $mappingData['mapping_group_name'] = $mappingModel->getGroupName();
                                $mappingData['mapping_priority'] = $mappingModel->getPriority();
                                $mappingData['attribute_code'] = $mappingModel->getAttributeCode();

                                $mainAttributeSet = $mappingData;
                                $processedAttributeCode[] = $mappingData['attribute_code'];
                            }
                        }
                    }
                }

                // CONTINUE GET ATTRIBUTE SET MAPPING BY OTHERS ATTRIBUTE
                if ($mainAttributeSet) {
                    $currentMapping = [];
                    
                    $attributeSetId = $mappingData['attribute_set_id'] ?? null;
                    $mappingPriority = $mappingData['mapping_priority'] ?? null;
                    $mappingGroupName = $mappingData['mapping_group_name'] ?? null;
                    if ($attributeSetId) {
                        while ($attributeSetId) {
                            if ($currentMapping) {
                                $mappingGroupName = $currentMapping['mapping_group_name'] ?? $mappingGroupName;
                                $mappingPriority = $currentMapping['mapping_priority'] ?? $mappingPriority;
                            }

                            $mapping = $this->mappingHelper->getMappingDataByAttributeSetId(
                                $rowData, 
                                $attributeSetId, 
                                $mappingGroupName,
                                $mappingPriority,
                                $processedAttributeCode
                            );

                            if (!$mapping) {
                                break;
                            }

                            $currentMapping = $mapping;

                            $mappingData = $mapping;
                            $processedAttributeCode[] = $mappingData['attribute_code'];
                            $attributeSetId = $mappingData['attribute_set_id'] ?? null;
                        }
                    }
                }

                if ($mappingData) {
                    $attributeSetId = $mappingData['attribute_set_id'] ?? null;
                    if ($attributeSetId) {
                        $mappingData['attribute_set_name'] = $this->mappingHelper->getAttributeSetNameById($attributeSetId);
                    }
                }

                // dd($processedAttributeCode);
                
                $this->cache->save(
                    serialize($mappingData),
                    $cacheKey,
                    [self::CACHE_TAG]
                );

                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;

                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/executionTimeexecutionTime.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('getAttributeSetMappingDataByCategoryName executionTime: '.$executionTime);

                return $mappingData;
            }
        }

        return null;
    }

    /**
     * Get cached website collection or load and cache it.
     *
     * @return array
     */
    public function getAttributeSetData($attributeSetId)
    {
        $startTime = microtime(true);

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeCollection = $objectManager->get('Magento\Eav\Model\Entity\Attribute');

        $cacheKey = self::CACHE_TAG . '_attribute_set_data_' . $attributeSetId;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $attributeSetData = [];

        // Get attribute set group
        $attributeGroupCollection = $this->attributeSetGroup->getCollection();
        $attributeGroupCollection->addFieldToFilter('attribute_set_id', $attributeSetId);
        if ($attributeGroupCollection->getSize()) {
            $attributeGroupIds = [];
            $categoryDetails = [];
            $dataAttributes = [];
            $categoryDetailsAttributeGroupId = null;
            foreach($attributeGroupCollection as $attributeGroup) {
                $attrGroupName = $attributeGroup->getAttributeGroupName();
                if ($attrGroupName == "Product Details" || $attrGroupName == "Category Details" || $attrGroupName == "Content") {
                    $attributeGroupIds[] = $attributeGroup->getId();
                }

                if ($attrGroupName == "Category Details") {
                    $categoryDetailsAttributeGroupId = $attributeGroup->getId();
                }

                $excludeAttributes = [
                    "category_ids",
                    "mtg_sealed_products",
                    "mtg_card_set",
                    "mtg_card_release",
                    "sealed_products_type"
                ];

                if ($attributeGroupIds) {
                    foreach($attributeGroupIds as $attributeGroupId) {
                        $attributeCollection = $this->attributeCollection->getCollection();
                        $attributeCollection->setAttributeSetFilter($attributeSetId);
                        $attributeCollection->setAttributeGroupFilter($attributeGroupId);
                        if ($attributeCollection->getSize()) {
                            foreach ($attributeCollection as $attribute) {
                                $attributeCode = $attribute->getAttributeCode();
                                $optionsArr = [];
                                $optionsData = [];
                                $optionsForSelection = [];
                                if ($attribute->getFrontendInput() == 'select') {
                                    $options = $attribute->getSource()->getAllOptions();
                                    if ($options) {
                                        foreach ($options as $option) {
                                            $label = $option['label'] ?? null;
                                            if ($label) {
                                                if (
                                                    $attributeCode == "card_product_type" 
                                                    && !str_contains($label, "Sealed Products") 
                                                    && !str_contains($label, "Singles")
                                                ) {
                                                    continue;
                                                }
                                            }

                                            if ($option['label'] && $option['label'] != ' ') {
                                                $optionsArr[] = $option['label'];
                                                $optionsForSelection[] = [
                                                    'text' => $option['label'],
                                                    'id' => $option['value']
                                                ];
                                                $optionsData[] = [
                                                    'label' => $option['label'],
                                                    'value' => $option['value']
                                                ];
                                            }
                                        }
                                    }
                                }

                                $exportAttributeCodes[] = $attributeCode;

                                $dataAttributes[] = [
                                    'attribute_code' => $attributeCode,
                                    'description' => $attribute->getFrontendLabel(),
                                    'options' => implode(', ', $optionsArr),
                                    'optionsData' => $optionsData
                                ];

                                if ($attributeGroupId == $categoryDetailsAttributeGroupId) {
                                    if (!$this->contains($attributeCode, $excludeAttributes)) {
                                        $categoryDetails[] = [
                                            'attribute_code' => $attributeCode,
                                            'attribute_label' => $attribute->getFrontendLabel(),
                                            'options' => $optionsForSelection
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $attributeSetData['categoryDetails'] = $categoryDetails;
            $attributeSetData['dataAttributes'] = $dataAttributes;
            $attributeSetData['exportAttributeCodes'] = $exportAttributeCodes;

            $this->cache->save(
                serialize($attributeSetData),
                $cacheKey,
                [self::CACHE_TAG]
            );

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/executionTimeexecutionTime.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('getAttributeSetData executionTime: '.$executionTime);

            return $attributeSetData;
        }

        return null;
    }

    public function contains($str, array $arr)
    {
        foreach($arr as $a) {
            if (stripos($str,$a) !== false) return true;
        }

        return false;
    }

    /**
     * Clear cache manually if needed.
     */
    public function clearCache()
    {
        $this->cache->remove(self::CACHE_TAG); // Remove cache entry
    }
}
