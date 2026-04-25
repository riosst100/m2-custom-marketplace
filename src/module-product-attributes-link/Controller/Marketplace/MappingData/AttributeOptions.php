<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MappingData;

use Magento\Framework\App\Action\Context;

class AttributeOptions extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;
    protected $cardGamesMappingCollFactory;
    protected $legoSetsFactory;


    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        parent::__construct(
            $context
        );
    }

    public function getAttributeIdByCode($code) 
    {
        return $this->eavAttribute->getIdByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            $code
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        // $defaultAttributeSetId = (int)$this->helper->getConfig("seller_settings/default_attribute_set_id");
        // $defaultAttributeSetId = $defaultAttributeSetId != 1 ? $defaultAttributeSetId : self::DEFAULT_ATTRIBUTE_SET_ID;

        $data = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // /* Create a new product object */
        // $this->customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $this->cardGamesMappingCollFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory::class);
        $this->legoSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\LegoSetsFactory::class);

        // $lastMappingAttribute = $this->customerSession->getLastMappingAttribute();
        // $lastMappingValue = $this->customerSession->getLastMappingValue();
        // if ($lastMappingAttribute) {
        //     // die($lastMappingAttribute);
        //     // die(print_r($lastMappingData,true));a
        // }

        // $mapping = null;

        // try {
        $selectedOptionId = $this->getRequest()->getParam('option_id');
        $attributeCode = $this->getRequest()->getParam('code');
        if ($selectedOptionId && $attributeCode) {
            if ($attributeCode == "bricks_set_name") {
                $collection = $this->legoSetsFactory->create()
                ->getCollection()
                ->addFieldToSelect(['bricks_theme','bricks_subtheme','bricks_set_number','release_year','number_of_pieces','bricks_character','release_date'])
                ->addFieldToFilter('bricks_set_name', $selectedOptionId);
                if ($collection->getSize()) {
                    // die('<pre>'.print_r($collection->getFirstItem()->getData(),true));
                    foreach($collection->getFirstItem()->getData() as $field => $mapping) {
                        if ($field != "entity_id") {
                            if (!in_array($field, ['release_date','bricks_set_number','number_of_pieces'])) {
                                if ($mapping) {
                                    $label = $this->getOptionLabelById($field, $mapping);
                                    $data[$field] = [
                                        [
                                        'label' => $label,
                                        'value' => $mapping
                                        ]
                                    ];
                                }
                            } else {
                                $data[$field] = $mapping;
                            }
                            // $data[] = [
                            //     'label' => $label,
                            //     'value' => $mapping
                            // ];
                            
                        }
                    }
                }
            }

            if ($attributeCode == "card_set") {

                $cardNameMapping = [];
                $setAbbrv = [];
                $collection = $this->cardGamesMappingCollFactory->create()
                ->getCollection()
                ->addFieldToFilter('card_set', $selectedOptionId);
                if ($collection->getSize()) {
                    $firstMap = $collection->getFirstItem();
                    $setAbbrv[] = [
                        'label' => $this->getOptionLabelById('card_set_abbreviation', $firstMap->getCardSetAbbreviation()),
                        'value' => $firstMap->getCardSetAbbreviation()
                    ];

                    foreach($collection as $mapping) {
                        $cardNameMapping[] = [
                            'label' => $this->getOptionLabelById('card_name', $mapping->getCardName()),
                            'value' => $mapping->getCardName()
                        ];
                    }
                }

                $data = [
                    'card_name' => $cardNameMapping,
                    'card_set_abbreviation' => $setAbbrv
                ];
            }

            if ($attributeCode == "card_name") {
                $collection = $this->cardGamesMappingCollFactory->create()
                ->getCollection()
                ->addFieldToFilter('card_name', $selectedOptionId);
                if ($collection->getSize()) {
                    $mappingData = $collection->getFirstItem()->getData();
                    foreach($mappingData as $field => $map) {
                        if ($field == "card_name" || $field == "entity_id" || $field == "card_game" || $field == "created_at" || $field == "updated_at" || $field == "card_set") {
                            continue;
                        }

                        $data[$field] = [
                            [
                            'label' => $this->getOptionLabelById($field, $map),
                            'value' => $map
                            ]
                        ];
                    }
                }
            }
        }

        //         $collection = $this->prdAttrLinkCollFactory->create()
        //         ->addFieldToFilter('attribute_code', $attributeCode)
        //         ->addFieldToFilter('mapping_type', 'attribute_set');
        //         // if ($collection->getSize() > 1) {
        //         //     if ($lastMappingAttribute != $attributeCode) {
        //         //         // $collection->addFieldToFilter('group_id', $selectedGroupId);
        //         //         // $collection->getSelect()->joinLeft(
        //         //         //     ['mpalg' => 'marketplace_product_attributes_link_group'],
        //         //         //     'mpalg.entity_id=main_table.group_id',
        //         //         //     []
        //         //         // )->where("mpalg.name='".$lastMappingValue."'");
        //         //     }
        //         // }
        //         // die('ffff'.$collection->getSize());
        //         if ($collection->getSize()) {
        //             foreach($collection as $attributeLink) {
        //                 if ($attributeLink->getAttributeOptionId() == $selectedOptionId) {
        //                     $mapping = $attributeLink;

        //                     $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
        //                     $data['mapping_group_id'] = $mapping->getGroupId();
        //                     $data['mapping_priority'] = $mapping->getPriority();
        
        //                     // Save temp data
        //                     $this->customerSession->setLastMappingAttribute($attributeCode);
        //                     $this->customerSession->setLastMappingValue($this->getOptionLabelById($attributeCode, $selectedOptionId));
        
        //                     $resultJson->setData(['success' => true, 'data' => $data]);
        //                     return $resultJson;
        //                 }
        //             }

        //             // $mapping = null;
        //             // foreach($collection as $attributeLink) {
        //             //     if ($attributeLink->getAttributeOptionId() == $selectedOptionId) {
        //             //         $mapping = $attributeLink;
        //             //     }
        //             // }

        //             // if ($mapping) {
        //             //     $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
        //             //     $data['mapping_group_id'] = $mapping->getGroupId();
        //             //     $data['mapping_priority'] = $mapping->getPriority();

        //             //     // Save temp data
        //             //     $this->customerSession->setLastMappingAttribute($attributeCode);
        //             //     $this->customerSession->setLastMappingValue($this->getOptionLabelById($attributeCode, $selectedOptionId));

        //             //     $resultJson->setData(['success' => true, 'data' => $data]);
        //             //     return $resultJson;
        //             // }

        //             $mappingGroupId = $this->getRequest()->getParam('mapping_group_id');
        //             $mappingPriority = $this->getRequest()->getParam('mapping_priority') ?: 1;
        //             if ($mappingGroupId) {
        //                 $collection = $this->prdAttrLinkCollFactory->create()
        //                 ->addFieldToFilter('group_id', $mappingGroupId)
        //                 ->addFieldToFilter('attribute_code', ['neq' => $attributeCode])
        //                 ->addFieldToFilter('priority', ['lteq' => $mappingPriority])
        //                 ->addFieldToFilter('mapping_type', 'attribute_set')
        //                 ->setOrder('priority', 'ASC');
        //                 // die($collection->getSelect()->__toString());
        //                 if ($collection->getSize()) {
        //                     // die('ada');
        //                     $mapping = $collection->getFirstItem();
        //                     if ($mapping) {
        //                         $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
        //                         $data['mapping_group_id'] = $mapping->getGroupId();
        //                         $data['mapping_priority'] = $mapping->getPriority();

        //                         $resultJson->setData(['success' => true, 'data' => $data]);
        //                         return $resultJson;
        //                     }
        //                 }
        //             }

        //             // die('cat');

        //             // Mapping from category
        //             $selectedCategoryId = $this->getRequest()->getParam('category_id');
        //             if ($selectedCategoryId) {
        //                 $collection = $this->prdAttrLinkCollFactory->create()
        //                 ->addFieldToFilter('category_id', $selectedCategoryId);
        //                 if ($collection->getSize()) {
        //                     $mapping = $collection->getFirstItem();
        //                     if ($mapping) {
        //                         $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
        //                         $data['mapping_group_id'] = $mapping->getGroupId();
        //                         $data['mapping_priority'] = $mapping->getPriority();

        //                         $resultJson->setData(['success' => true, 'data' => $data]);
        //                         return $resultJson;
        //                     }
        //                 }
        //             }

        //             // Default attribute set
        //             $data['attribute_set_id'] = $defaultAttributeSetId;
        //             $data['mapping_group_id'] = $mapping->getGroupId();
        //             $data['mapping_priority'] = $mapping->getPriority();

        //             $resultJson->setData(['success' => true, 'data' => $data]);
        //             return $resultJson;
        //         }
        //     }
        // } catch (\Exception $e) {}

        $resultJson->setData(['success' => true, 'data' => $data]);
        return $resultJson;
    }

    public function getOptionLabelById($attributeCode, $optionId) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

            $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

            $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
            $optionlabel = $attribute->getSource()->getOptionText($optionId);

        return $optionlabel;
    }
}