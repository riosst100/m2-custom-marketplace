<?php
namespace TCGCollective\MarketPlace\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class BulkImportData extends Action
{
    protected $mappingHelper;
    protected $storeManager;
    protected $sellerHelper;
    protected $csvProcessor;
    protected $fileSystem;
    protected $_frontendUrl;


    protected $resultJsonFactory;
    protected $helper;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \TCGCollective\MarketplaceProductImportExport\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
        $mappingPriority = $this->getRequest()->getParam('mapping_priority') ?: 1;
        $currentAttributeCode = $this->getRequest()->getParam('code');

        $selectedOptionId = $this->getRequest()->getParam('option_id');
        $selectedGroupId = $this->getRequest()->getParam('mapping_group_id');
        $selectedAttributeCode = $this->getRequest()->getParam('mapping_attribute_code');
        $selectedGroupName = $this->getRequest()->getParam('mapping_group_name');

        $attributeSetId = $this->getRequest()->getParam('attribute_set_id') ?? null;

        $source = $this->getRequest()->getParam('source') == "database" ? "database" : "catalog";

        $data = [
            'attribute_set_id' => $attributeSetId ?? null,
            'mapping_group_id' => $selectedGroupId ?? null,
            'mapping_group_name' => $selectedGroupName ?? null,
            'mapping_attribute_code' => $selectedAttributeCode ?? null,
            'mapping_priority' => $mappingPriority ?? null
        ];

        $dataAttributes = [];
        $exportAttributesData = [];
        $categoryDetails = [];
        $categoryDetailsAttributeGroupId = null;
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

        $selectedCategoryId = $this->getRequest()->getParam('categories') ?? null;
        $currentAttributeSet = $this->getRequest()->getParam('_attribute_set') ?? null;
        
        if ($selectedCategoryId) {
            if ($currentAttributeCode != "set_id" && $currentAttributeCode != "card_release") {
                $data = $this->mappingHelper->getAttributeSetData($currentAttributeCode, $mappingPriority, $selectedOptionId, $selectedGroupId, $selectedCategoryId, $currentAttributeSet, $selectedGroupName, $selectedAttributeCode);
                if ($data) {
                    $attributeSetId = $data['attribute_set_id'] ?? null;
                    $mappingGroupId = $data['mapping_group_id'] ?? null;
                    $mappingGroupName = $data['mapping_group_name'] ?? null;
                    $selectedAttributeCode = $data['mapping_attribute_code'] ?? null;
                    $mappingPriority = $data['mapping_priority'] ?? null;
                }
            }
            
            // dd($attributeSetId);
            
            if ($attributeSetId) {
                // dd($data);
                // $attributeSetId = 20;//your_attributeSetId
                // dd($attributeSetId);
                $attributeSet = $objectManager->create('Magento\Eav\Api\AttributeSetRepositoryInterface');
                $attributeSetRepository = $attributeSet->get($attributeSetId);

                $attribute_set_name = $attributeSetRepository->getAttributeSetName();

                // dd($attribute_set_name);

                $attributeGroupCollection = $objectManager->get('Magento\Eav\Model\Entity\Attribute\Group')->getCollection();
                // $attributeGroupCollection->addFieldToFilter('attribute_group_name', $attribute_set_name);
                $attributeGroupCollection->addFieldToFilter('attribute_set_id', $attributeSetId);
                $attributeGroupId = '';
                foreach ( $attributeGroupCollection as $attributeGroup ) {
                    $attrGroupName = $attributeGroup->getAttributeGroupName();
                    if ($attrGroupName == "Product Details" || $attrGroupName == "Category Details" || $attrGroupName == "Content") {
                        $attributeGroupIds[] = $attributeGroup->getId();
                    }

                    if ($attrGroupName == "Category Details") {
                        $categoryDetailsAttributeGroupId = $attributeGroup->getId();
                    }
                }

                $excludeAttributes = [
                    "category_ids",
                    // "mtg_sealed_products",
                    // "mtg_card_set",
                    // "mtg_card_release",
                    "card_release",
                    "sealed_products",
                    "card_set",
                    "sealed_products_type"
                ];

                if ($attributeGroupIds) {
                    foreach($attributeGroupIds as $attributeGroupId) {
                        $attributeCollection = $objectManager->get('Magento\Eav\Model\Entity\Attribute')->getCollection();
                        $attributeCollection->setAttributeSetFilter($attributeSetId);
                        $attributeCollection->setAttributeGroupFilter($attributeGroupId);
                        if ($attributeCollection->getSize() > 0) {
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

                                $exportAttributesData[] = $attributeCode;

                                $dataAttributes[] = [
                                    'attribute_code' => $attributeCode,
                                    'description' => $attribute->getFrontendLabel(),
                                    'options' => implode(', ', $optionsArr)
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
        }

        // dd($exportAttributesData);

        $exportedFileUrl = null;

        // CUSTOM TABLE METADATA BASED ON SELECTED CATEGORIES
        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata($selectedCategoryId);
        $mainAttrCode = $customTableMetadata['mainAttrCode'];
        $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'];
        $mainReleaseAttrCode = $customTableMetadata['mainReleaseAttrCode'];
        $mainSetsNumberAttrCode = $customTableMetadata['mainSetsNumberAttrCode'];
        $mainSinglesAttrCode = $customTableMetadata['mainSinglesAttrCode'];
        $mainSinglesNumberAttrCode = $customTableMetadata['mainSinglesNumberAttrCode'];
        $singlesCollection = $customTableMetadata['singlesCollection'];
        $setsCollection = $customTableMetadata['setsCollection'];
        $sealedProductsCollection = $customTableMetadata['sealedProductsCollection'];
        $setsTableName = $customTableMetadata['setsTableName'];
        $singlesTableName = $customTableMetadata['singlesTableName'];
        $setsFieldToSelet = $customTableMetadata['setsFieldToSelet'];

        // dd($setsFieldToSelet);

        $setId  = $this->getRequest()->getParam('set_id');
        $cardRelease  = $this->getRequest()->getParam('card_release');
        $code  = $this->getRequest()->getParam('code');
        $option_id  = $this->getRequest()->getParam('option_id');

        if ($setId && !$cardRelease) {
            $optionLabel = $code == "card_product_type" && $option_id ? $this->mappingHelper->getOptionLabelById($code, $option_id) : null;
            if ($code == "set_id" || $code == "card_product_type" && str_contains($optionLabel, "Singles")) {
                $isSingles = true;
                $categoryDetails[] = [
                    'attribute_code' => 'set_id',
                    'attribute_label' => 'Sets',
                    'options' => []
                ];
                $exportedFileUrl = $this->exportSinglesProduct($source, $attribute_set_name, $selectedCategoryId, $setsFieldToSelet, $mainAttrCode, $setsTableName, $mainSinglesNumberAttrCode, $mainSinglesAttrCode, $mainSetsNumberAttrCode, $mainSetsAttrCode, $exportAttributesData, $singlesCollection, $setId);
            }

            // dd($categoryDetails);
        }

        
        if ($cardRelease && !$setId) {
            $optionLabel = $code == "card_product_type" && $option_id ? $this->mappingHelper->getOptionLabelById($code, $option_id) : null;
            if ($code == "card_product_type" && str_contains($optionLabel, "Sealed Products") || $code == "card_release") {
                $isSingles = false;
                $categoryDetails[] = [
                    'attribute_code' => 'card_release',
                    'attribute_label' => 'Release',
                    'options' => []
                ];
                $exportedFileUrl = $this->exportSealedProducts($source, $attribute_set_name, $selectedCategoryId, $setsFieldToSelet, $mainAttrCode, $setsTableName, $mainSinglesNumberAttrCode, $mainSinglesAttrCode, $mainSetsNumberAttrCode, $mainSetsAttrCode, $exportAttributesData, $sealedProductsCollection, $cardRelease);
            }
        }
        
        if ($currentAttributeCode && $selectedOptionId) {
            $productTypeCode = $currentAttributeCode;
            $productTypeVal = $selectedOptionId;

            $entityCode = null;
            $entityVal = null;

            if ($currentAttributeCode == "toys_line") {
                $entityCode = 'toys_brand';
                $entityVal = '';
            }

            if ($currentAttributeCode == "card_product_type") {
                $entityCode = 'card_game';
                $entityVal = $this->getRequest()->getParam($entityCode);
            }

            $setsSelection = [];
            
            $customTableDetails = $this->mappingHelper->getCustomTableDetails($productTypeCode, $productTypeVal, $entityCode, $entityVal, 'simple');
            if ($customTableDetails) {
                $is_custom_table_exist = $customTableDetails['is_custom_table_exist'] ?? false;
                if ($is_custom_table_exist) {
                    $isSingles = $customTableDetails['is_singles'] ?? false;

                    // Handle singles release
                    if ($isSingles) {
                        // Get all sets data for selection
                        $setId  = $this->getRequest()->getParam('set_id');
                        if (!$setId) {
                            $setsOptions = [];

                            if ($setsCollection) {
                                $setsCollection->addFieldToFilter($mainAttrCode, $this->getRequest()->getParam($mainAttrCode));
                                // $singlesCollection->addFieldToFilter('set_id', $this->getRequest()->getParam($mainAttrCode));
                                // dd($singlesCollection->getSelect()->__toString());

                                $languageVersion = $this->getRequest()->getParam('language_version');
                                if ($languageVersion) {
                                    $languageVersionLabel = $this->mappingHelper->getOptionLabelById('language_version', $languageVersion);
                                    if ($languageVersionLabel) {
                                        $setsCollection->addFieldToFilter('main_table.language_version', $languageVersionLabel);
                                    }
                                }

                                $setsCollection->getSelect()->join(
                                    ['singles' => $singlesTableName],
                                    'main_table.entity_id = singles.set_id',
                                    []
                                );

                                $setsCollection->getSelect()->group('main_table.entity_id');
                                $setsCollection->setOrder('main_table.'.$mainSetsAttrCode, 'ASC');

                                if ($setsCollection->getSize()) {
                                    foreach($setsCollection as $setsData) {
                                        $setsOptions[] = [
                                            'text' => $setsData->getData($mainSetsAttrCode), 
                                            'id' => $setsData->getData('entity_id')
                                        ];
                                    }
                                }
                            }

                            $categoryDetails[] = [
                                'attribute_code' => 'set_id',
                                'attribute_label' => 'Sets',
                                'options' => $setsOptions
                            ];
                        }
                    }

                    if (!$isSingles) {
                        $cardReleaseOptions = [];

                        if ($sealedProductsCollection) {

                            $sealedProductsCollection->getSelect()->join(
                                ['sets' => $setsTableName],
                                'main_table.card_release = sets.card_release',
                                []
                            );

                            $languageVersion = $this->getRequest()->getParam('language_version');
                            if ($languageVersion) {
                                    $languageVersionLabel = $this->mappingHelper->getOptionLabelById('language_version', $languageVersion);
                                //     if ($languageVersionLabel) {
                                //         $sealedProductsCollection->addFieldToFilter('main_table.language_version', $languageVersionLabel);
                                //     }
                                // }
                                $sealedProductsCollection->getSelect()->where('sets.language_version="'.$languageVersionLabel.'"');
                            }

                            $sealedProductsCollection->addFieldToFilter('sets.'.$mainAttrCode, $this->getRequest()->getParam($mainAttrCode));

                            $sealedProductsCollection->getSelect()->group('main_table.card_release');

                            $sealedProductsCollection->setOrder('main_table.'.$mainReleaseAttrCode, 'ASC');

                            if ($sealedProductsCollection->getSize()) {
                                foreach($sealedProductsCollection as $setsData) {
                                    $cardReleaseOptions[] = [
                                        'text' => $setsData->getData($mainReleaseAttrCode), 
                                        'id' => $setsData->getData($mainReleaseAttrCode)
                                    ];
                                }
                            }
                        }

                        // dd($cardReleaseOptions);

                        $categoryDetails[] = [
                            'attribute_code' => $mainReleaseAttrCode,
                            'attribute_label' => 'Release',
                            'options' => $cardReleaseOptions
                        ];
                    }
                }
            }
        }

        // $mappingPriority = $this->getRequest()->getParam('mapping_priority') ?: 1;
            $attributeCode = $this->getRequest()->getParam('code');

        //     $selectedOptionId = $this->getRequest()->getParam('option_id');
        //     $selectedGroupId = $this->getRequest()->getParam('mapping_group_id');

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result = [
            'fields_information_html' => $resultPage->getLayout()
                ->createBlock('Magento\Framework\View\Element\Template')
                ->setTemplate('TCGCollective_MarketPlace::bulk_import_fields_information.phtml')
                ->setData('attributes_data', $dataAttributes)
                ->toHtml(),
            'attribute_set_id' => $attributeSetId,
            '_attribute_set' => $attribute_set_name ?? '',
            'category_details' => $categoryDetails,
            'mapping_data' => $data,
            'code' => $attributeCode,
            'template_url' => $exportedFileUrl
        ];

        return $this->resultJsonFactory->create()->setData($result);
    }
    
    public function exportSealedProducts($source, $attributeSetId, $categoryId, $setsFieldToSelet, $mainAttrCode, $setsTableName, $mainSinglesNumberAttrCode, $mainSinglesAttrCode, $mainSetsNumberAttrCode, $mainSetsAttrCode, $exportAttributesData, $sealedProductsCollection, $cardRelease) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);

        // get seller id om
        $sellerId = $this->sellerHelper->getSellerId();

        if ($sealedProductsCollection) {
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($value) {
                return $value !== "name";
            });

            // Optional: reset key agar rapi (jika perlu)
            $setsFieldToSelet = array_values($setsFieldToSelet);
            // dd($setsFieldToSelet);
            $sealedProductsCollection->addFieldToSelect('name');
            $sealedProductsCollection->addFieldToFilter('main_table.card_release', $cardRelease);
            $sealedProductsCollection->getSelect()->joinLeft(
                ['sets' => $setsTableName],
                'main_table.card_release=sets.card_release',
                $setsFieldToSelet
            )->group('main_table.entity_id');

            // dd($sealedProductsCollection->getSelect()->__toString());
            
            if ($sealedProductsCollection->getSize()) {
                $productsToExport = [];

                foreach($sealedProductsCollection as $index => $sealedProductsData) {
                    $productData = [];

                    // dd($sealedProductsData);

                    // PUBLISH DATA
                    $defaultWebsite = $this->storeManager->getStore()->getWebsite()->getName();
                    $productData['publish_status'] = 0;
                    $productData['listing_country'] = $defaultWebsite;
                    $productData['price'] = 0;
                    $productData['qty'] = 0;

                    // PRODUCT DETAILS
                    $productIdentifierArr = [];
                    // $productNameArr = [];

                    $sealedProducts = $sealedProductsData['sealed_products'] ?? null;
                    // if ($sealedProducts) {
                    //     // $cardIdentifierArr = [];
                    //     // $productIdentifierArr[] = implode("", $cardIdentifierArr);
                    //     $setsIdentifierArr[] = strtoupper($this->convertToSlug($sealedProducts));
                    //     $productNameArr[] = $sealedProducts;
                    // }

                    $releaseName = $sealedProductsData['card_release'] ?? null;
                    // if ($releaseName) {
                    //     // $setsIdentifierArr = [];
                    //     // $setAbbrv = $sealedProductsData[$mainSetsNumberAttrCode] ?? null;
                    //     // if ($setAbbrv) {
                    //         $setsIdentifierArr[] = strtoupper($this->convertToSlug($releaseName));
                    //     //     $setsName = $setsName . ' (' . $setAbbrv . ')';
                    //     // }

                    //     // $productIdentifierArr[] = implode("", $setsIdentifierArr);
                    //     $productNameArr[] = $releaseName;
                    // }

                    $mainAttrVal = $this->mappingHelper->getOptionLabelById($mainAttrCode, $sealedProductsData[$mainAttrCode]);

                    $productData[$mainAttrCode] = $mainAttrVal ? trim($mainAttrVal) : '';

                    $mainAttrVal = $productData[$mainAttrCode] ?? null;
                    // if ($mainAttrVal) {
                    //     $productNameArr[] = $mainAttrVal;
                    // }

                    

                    // $productName = implode(" | ", $productNameArr);
                    // dd($productName);
                    if ($sealedProductsData) {
                        $productData['name'] = $sealedProductsData->getData('name') ?? '';
                    }

                    // dd($productData['name']);

                    $productIdentifierArr[] = strtoupper($this->makeid(10));

                    $productIdentifier = implode("", $productIdentifierArr);
                    
                    if ($productIdentifier) {
                        $productData['supplier_sku'] = trim($productIdentifier);
                    }

                    $imagesData = '';
                    $thumbData = '';
                    
                    $imagesDataRaw = $sealedProductsData->getImages();
                    if ($imagesDataRaw) {
                        $imagesDataArr = explode(",", $imagesDataRaw);
                        if ($imagesDataArr) {
                            $newImagesDataArr = [];
                            foreach($imagesDataArr as $key => $imageWithPath) {
                                if ($imageWithPath) {
                                    if ($key == 0) {
                                        $thumbData = basename($imageWithPath);
                                    } else {
                                        $newImagesDataArr[] = basename($imageWithPath);
                                    }
                                }
                            }

                            if ($newImagesDataArr) { 
                                $imagesData = implode(",", $newImagesDataArr);
                            }
                        }
                    }
                    
                    // IMAGES
                    $productData['base_image'] = (string)trim($thumbData);
                    $productData['additional_images'] = (string)trim($imagesData);

                    // Get prefix
                    $categoryId = $this->getRequest()->getParam('categories') ?? null;
                    $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, null, $mainAttrVal, $mainAttrCode);

                    // dd($prefix);

                    $mainSetsAttrVal = $sealedProductsData[$mainSetsAttrCode] ?? '';
                    $mainSealedProductsVal = $sealedProductsData['card_release'] ?? '';

                    // dd($exportAttributesData);

                    // SETS / SINGLES DATA
                    foreach($exportAttributesData as $attrCode) {

                        if ($attrCode == $mainAttrCode) {
                            continue;
                        }
                        
                        $attrCodeWithoutPrefix = null;

                        if ($prefix && str_contains($attrCode, $prefix)) {
                            $attrCodeWithoutPrefix = str_replace($prefix, "", $attrCode);
                        }

                        // if ($attrCodeWithoutPrefix == "card_release") {
                        // dd($attrCodeWithoutPrefix);
                        // }
                        
                        if ($attrCodeWithoutPrefix) {
                            $value = $sealedProductsData->getData($attrCodeWithoutPrefix) ?? '';
                            if ($value) {
                                try {
                                    $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                                } catch(\Exception $e) {}
                            }
                            
                            $productData[$attrCode] = $value ? trim($value) : '';
                        } else {
                            $value = $sealedProductsData->getData($attrCode) ?? '';
                            if ($value) {
                                try {
                                    $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                                } catch(\Exception $e) {}
                                $productData[$attrCode] = $value ? trim($value) : '';
                            } else {
                                if (!isset($productData[$attrCode])) {
                                    if ($attrCode == "price" || $attrCode == "publish_status") {
                                        $productData[$attrCode] = 0;
                                    }
                                    $productData[$attrCode] = '';
                                }
                            }
                        }
                    }
                    
                    $keysToUnset = ['category_ids', 'status', 'required_options', 'has_options', 'image_label','quantity_and_stock_status','visibility','seller_id','approval','seller_group','membership_order',
                    'seller_featured_package',
                    'auction',
                    'sale',
                    'links_purchased_separately',
                    'samples_title',
                    'links_title',
                    'links_exist',
                    'source',
                    'thumbnail_label',
                    'featured_package',
                    'weight',
                    'weight_type',
                    'news_from_date',
                    'news_to_date',
                    'lof_preorder',
                    'preorder_availability',
                    'preorder_msg',
                    'cod_available',
                    'link_to_other_stores',
                    'product_rma',
                    'seller_duration',
                    'seller_membership_order',
                    'small_image_label','created_at','updated_at','sku_type','price_type','tax_class_id','old_id', 'url_path','card_game_mtg','card_product_type'];
                    $productData = array_diff_key($productData, array_flip($keysToUnset));
                    
                    unset($productData['sku']);

                    // PARAMS DATA
                    $paramData = $this->getRequest()->getParams();
                    // dd($paramData);
                    if ($paramData) {

                        $unusedParams = [
                            "form_key",
                            "entity",
                            "template_url",
                            "behavior",
                            "validation_strategy",
                            "allowed_error_count",
                            "_import_field_separator",
                            "_import_multiple_value_separator",
                            "import_images_file_dir",
                            "option_id",
                            "code","source","mapping_group_id","mapping_priority",
                            "attribute_set_id",
                            "set_id",
                            "card_release",
                            "_attribute_set"
                        ];
                        
                        foreach($paramData as $field => $param) {
                            if (!in_array($field, $unusedParams)) {
                                if ($field == "categories") {
                                    $category = $this->mappingHelper->getCategoryById($param);
                                    if ($category) {
                                        $categoryPathIds = $category->getPathIds();
                                        if ($categoryPathIds) {
                                            $categoryPathName = $this->mappingHelper->getCategoryLabelByIds($categoryPathIds);
                                            if ($categoryPathName) {
                                                $productData[$field] = $categoryPathName ? trim($categoryPathName) : '';
                                            }
                                        }
                                    }
                                } else {
                                    $label = $param ? $this->mappingHelper->getOptionLabelById($field, $param) : '';
                                    $productData[$field] = $label ? trim($label) : '';
                                }
                            }
                        }
                    }

                    $productsToExport[] = $productData;
                }
                
                // die('<pre>'.print_r($productsToExport,true));

                $header = array_keys($productsToExport[0]);

                array_unshift($productsToExport, $header);

                if ($productsToExport) {
                    return $this->exportProductAsCsv('sealedproducts', $productsToExport, $source, $mainAttrCode, $mainAttrVal, $mainSetsAttrVal, $mainSealedProductsVal);
                }
            }
        }

        return null;
    }

    public function exportSinglesProduct($source, $attributeSetId, $categoryId, $setsFieldToSelet, $mainAttrCode, $setsTableName, $mainSinglesNumberAttrCode, $mainSinglesAttrCode, $mainSetsNumberAttrCode, $mainSetsAttrCode, $exportAttributesData, $singlesCollection, $setId) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);

        // get seller id om
        $sellerId = $this->sellerHelper->getSellerId();

        if ($singlesCollection) {
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($value) {
                return $value !== "name";
            });

            // Optional: reset key agar rapi (jika perlu)
            $setsFieldToSelet = array_values($setsFieldToSelet);
            // dd($setsFieldToSelet);

            $singlesCollection->addFieldToFilter('set_id', $setId);
            $singlesCollection->getSelect()->joinLeft(
                ['sets' => $setsTableName],
                'main_table.set_id=sets.entity_id',
                $setsFieldToSelet
            );
            
            if ($singlesCollection->getSize()) {
                $productsToExport = [];

                foreach($singlesCollection as $index => $singlesData) {
                    $productData = [];

                    // dd($singlesData);

                    // PUBLISH DATA
                    $defaultWebsite = $this->storeManager->getStore()->getWebsite()->getName();
                    $productData['publish_status'] = 0;
                    $productData['listing_country'] = $defaultWebsite;
                    $productData['price'] = 0;
                    $productData['qty'] = 0;

                    // PRODUCT DETAILS
                    $productIdentifierArr = [];
                    // $productNameArr = [];

                    $cardName = $singlesData[$mainSinglesAttrCode] ?? null;
                    // if ($cardName) {
                    //     $cardIdentifierArr = [];
                    //     $cardNumber = $sidnglesData[$mainSinglesNumberAttrCode] ?? null;
                    //     if ($cardNumber) {
                    //         $cardIdentifierArr[] = strtoupper($this->convertToSlug($cardNumber));
                    //         $cardName = $cardName . ' ' . '#' . $cardNumber;
                    //     }

                    //     $productIdentifierArr[] = implode("", $cardIdentifierArr);
                    //     $productNameArr[] = $cardName;
                    // }

                    $setsName = $singlesData[$mainSetsAttrCode] ?? null;
                    // if ($setsName) {
                    //     $setsIdentifierArr = [];
                    //     $setAbbrv = $singlesData[$mainSetsNumberAttrCode] ?? null;
                    //     if ($setAbbrv) {d
                    //         $setsIdentifierArr[] = strtoupper($this->convertToSlug($setAbbrv));
                    //         $setsName = $setsName . ' (' . $setAbbrv . ')';
                    //     }

                    //     $productIdentifierArr[] = implode("", $setsIdentifierArr);
                    //     $productNameArr[] = $setsName;
                    // }

                    $mainAttrVal = $this->mappingHelper->getOptionLabelById($mainAttrCode, $singlesData[$mainAttrCode]);

                    $productData[$mainAttrCode] = $mainAttrVal ? trim($mainAttrVal) : '';

                    $mainAttrVal = $productData[$mainAttrCode] ?? null;
                    // if ($mainAttrVal) {
                    //     $productNameArr[] = $mainAttrVal;
                    // }

                    

                    // $productName = implode(" | ", $productNameArr);
                    // if ($productName) {
                    //     $productData['name'] = trim($productName);
                    // }
                    if ($singlesData) {
                        $productData['name'] = $singlesData->getData('name') ?? '';
                    }

                    $productIdentifierArr[] = strtoupper($this->makeid(10));

                    $productIdentifier = implode("", $productIdentifierArr);
                    
                    if ($productIdentifier) {
                        $productData['supplier_sku'] = trim($productIdentifier);
                    }

                    $imagesData = '';
                    $thumbData = '';
                    
                    $imagesDataRaw = $singlesData->getImages();
                    if ($imagesDataRaw) {
                        $imagesDataArr = explode(",", $imagesDataRaw);
                        if ($imagesDataArr) {
                            $newImagesDataArr = [];
                            foreach($imagesDataArr as $key => $imageWithPath) {
                                if ($imageWithPath) {
                                    if ($key == 0) {
                                        $thumbData = basename($imageWithPath);
                                    } else {
                                        $newImagesDataArr[] = basename($imageWithPath);
                                    }
                                }
                            }

                            if ($newImagesDataArr) { 
                                $imagesData = implode(",", $newImagesDataArr);
                            }
                        }
                    }
                    
                    // IMAGES
                    $productData['base_image'] = (string)trim($thumbData);
                    $productData['additional_images'] = (string)trim($imagesData);

                    // Get prefix
                    $categoryId = $this->getRequest()->getParam('categories') ?? null;
                    $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, null, $mainAttrVal, $mainAttrCode);

                    $mainSetsAttrVal = $singlesData[$mainSetsAttrCode] ?? '';

                    // dd($exportAttributesData);

                    // SETS / SINGLES DATA
                    foreach($exportAttributesData as $attrCode) {

                        if ($attrCode == $mainAttrCode) {
                            continue;
                        }
                        
                        $attrCodeWithoutPrefix = null;

                        if ($prefix && str_contains($attrCode, $prefix)) {
                            $attrCodeWithoutPrefix = str_replace($prefix, "", $attrCode);
                        }

                        // if ($attrCodeWithoutPrefix == "card_release") {
                        // dd($attrCodeWithoutPrefix);
                        // }
                        
                        if ($attrCodeWithoutPrefix) {
                            $value = $singlesData->getData($attrCodeWithoutPrefix) ?? '';
                            if ($value) {
                                try {
                                    $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                                } catch(\Exception $e) {}
                            }
                            
                            $productData[$attrCode] = $value ? trim($value) : '';
                        } else {
                            $value = $singlesData->getData($attrCode) ?? '';
                            if ($value) {
                                try {
                                    $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                                } catch(\Exception $e) {}
                                $productData[$attrCode] = $value ? trim($value) : '';
                            } else {
                                if (!isset($productData[$attrCode])) {
                                    if ($attrCode == "price" || $attrCode == "publish_status") {
                                        $productData[$attrCode] = 0;
                                    }
                                    $productData[$attrCode] = '';
                                }
                            }
                        }
                    }
                    
                    $keysToUnset = ['category_ids', 'status', 'required_options', 'has_options', 'image_label','quantity_and_stock_status','visibility','seller_id','approval','seller_group','membership_order',
                    'seller_featured_package',
                    'auction',
                    'sale',
                    'links_purchased_separately',
                    'samples_title',
                    'links_title',
                    'links_exist',
                    'source',
                    'thumbnail_label',
                    'featured_package',
                    'weight',
                    'weight_type',
                    'news_from_date',
                    'news_to_date',
                    'lof_preorder',
                    'preorder_availability',
                    'preorder_msg',
                    'cod_available',
                    'link_to_other_stores',
                    'product_rma',
                    'seller_duration',
                    'seller_membership_order',
                    'small_image_label','created_at','updated_at','sku_type','price_type','tax_class_id','old_id', 'url_path','card_game_mtg','card_product_type'];
                    $productData = array_diff_key($productData, array_flip($keysToUnset));
                    
                    unset($productData['sku']);

                    // PARAMS DATA
                    $paramData = $this->getRequest()->getParams();
                    // dd($paramData);
                    if ($paramData) {

                        $unusedParams = [
                            "form_key",
                            "entity",
                            "template_url",
                            "behavior",
                            "validation_strategy",
                            "allowed_error_count",
                            "_import_field_separator",
                            "_import_multiple_value_separator",
                            "import_images_file_dir",
                            "option_id",
                            "code","source","mapping_group_id","mapping_priority",
                            "attribute_set_id",
                            "set_id",
                            "_attribute_set"
                        ];
                        
                        foreach($paramData as $field => $param) {
                            if (!in_array($field, $unusedParams)) {
                                if ($field == "categories") {
                                    $category = $this->mappingHelper->getCategoryById($param);
                                    if ($category) {
                                        $categoryPathIds = $category->getPathIds();
                                        if ($categoryPathIds) {
                                            $categoryPathName = $this->mappingHelper->getCategoryLabelByIds($categoryPathIds);
                                            if ($categoryPathName) {
                                                $productData[$field] = $categoryPathName ? trim($categoryPathName) : '';
                                            }
                                        }
                                    }
                                } else {
                                    $label = $param ? $this->mappingHelper->getOptionLabelById($field, $param) : '';
                                    $productData[$field] = $label ? trim($label) : '';
                                }
                            }
                        }
                    }

                    $productsToExport[] = $productData;
                }
                
                // die('<pre>'.print_r($productsToExport,true));

                $header = array_keys($productsToExport[0]);

                array_unshift($productsToExport, $header);

                if ($productsToExport) {
                    return $this->exportProductAsCsv('singles', $productsToExport, $source, $mainAttrCode, $mainAttrVal, $mainSetsAttrVal);
                }
            }
        }

        return null;
    }

    public function makeId($length) 
    {
        $result = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, $charactersLength - 1)];
        }

        return $result;
    }    

    public function convertToSlug($text) 
    {
        return strtolower(
            preg_replace(
                ['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'],
                ['', '-', ''],
                trim($text)
            )
        );
    }

    public function exportProductAsCsv($customTableName, $productData, $source, $mainAttrCode, $mainAttrVal, $mainSetsAttrVal, $mainSealedProductsVal = null) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->csvProcessor = $objectManager->get('\Magento\Framework\File\Csv'); 
        $this->fileSystem = $objectManager->get('\Magento\Framework\Filesystem');
        $this->_frontendUrl = $objectManager->create(\Magento\Framework\UrlInterface::class);

        $fileName = '';

        if ($mainAttrVal) {
            $fileName .= $this->mappingHelper->convertToFileName($mainAttrVal);
        }

        if ($customTableName == "singles") {
            if ($mainSetsAttrVal) {
                $fileName .= '-' . $this->mappingHelper->convertToFileName($mainSetsAttrVal);
            }
        }

        if ($customTableName == "sealedproducts") {
            if ($mainSealedProductsVal) {
                $fileName .= '-' . $this->mappingHelper->convertToFileName($mainSealedProductsVal);
            }
        }

        $fileName .= '__' . $this->mappingHelper->getDateTime('Y-m-d') . '_' . $this->mappingHelper->getDateTime('H-i-s') . '.csv';

        $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        $filePath = $varDirectory->getAbsolutePath($fileName);
        if ($productData) {
            $this->csvProcessor->saveData($filePath, $productData);

            // // Tambahkan BOM di awal file CSV
            // $fileContent = file_get_contents($filePath);
            // $fileContentWithBom = "\xEF\xBB\xBF" . $fileContent; // Tambahkan BOM
            // file_put_contents($filePath, $fileContentWithBom);  
            // Membuka file untuk menulis dengan mode 'w'
            $handle = fopen($filePath, 'w');

            // Menambahkan BOM di awal
            fwrite($handle, "\xEF\xBB\xBF");

            // Menulis data produk ke file CSV
            foreach ($productData as $row) {
                fputcsv($handle, $row);
            }

            // Menutup file
            fclose($handle);

            $fileUrl = $this->_frontendUrl->getUrl('*/export/download', ['filename' => $fileName]);
            return $fileUrl;
        }

        return null;
    }

    public function exportCustomTablesAsProduct($selectedCategoryId, $isSingles) 
    {
        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata($selectedCategoryId);
        // dd($customTableMetadata);
        if ($customTableMetadata) {
            $mainAttrCode = $customTableMetadata['mainAttrCode'];
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'];
            $mainSinglesAttrCode = $customTableMetadata['mainSinglesAttrCode'];
            $singlesCollection = $customTableMetadata['singlesCollection'];
            $setsCollection = $customTableMetadata['setsCollection'];
            $setsTableName = $customTableMetadata['setsTableName'];
            $setsFieldToSelet = $customTableMetadata['setsFieldToSelet'];

            // GET SINGLES DATA
            if ($isSingles) {
                if ($singlesCollection) {
                    // $singlesCollection->addFieldToFilter($mainAttrCode, $this->getRequest()->getParam($mainAttrCode));
                    // $singlesCollection->addFieldToFilter('set_id', $this->getRequest()->getParam($mainAttrCode));
                    dd($singlesCollection->getSelect()->__toString());
                    if ($setId) {
                        if ($singlesCollection->getSize()) {
                            foreach($singlesCollection as $singlesData) {
                                dd($singlesData);
                            }
                        }
                    }
                }
            } else {
                // GET SINGLES DATA
                if ($setsCollection) {
                    $setsCollection->addFieldToFilter($mainAttrCode, $this->getRequest()->getParam($mainAttrCode));
                    // $singlesCollection->addFieldToFilter('set_id', $this->getRequest()->getParam($mainAttrCode));
                    // dd($singlesCollection->getSelect()->__toString());
                    if ($setsCollection->getSize()) {
                        foreach($setsCollection as $setsData) {
                            dd($setsData);
                        }
                    }
                }
            }
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
}