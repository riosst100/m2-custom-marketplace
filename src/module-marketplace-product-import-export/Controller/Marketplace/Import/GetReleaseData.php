<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Import;

use Magento\Backend\App\Action;

class GetReleaseData extends \Magento\Customer\Controller\AbstractAccount
{
    protected $customAttributesFactory;


    protected $helper;
    protected $jsonResultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customAttributesFactory = $objectManager->get('\TCGCollective\ProductAttributesLink\Model\CustomAttributesFactory');

        $data = [
            'attribute' => [],
            'data' => []
        ];
        
        $attrCode = null;
        $entityCode = $this->getRequest()->getParam('subcategories_attr_code') ?: null;
        $entityVal = $this->getRequest()->getParam('subcategories') ?: null;

        if ($entityCode && $entityVal) {
            // Toys Brands
            if ($entityCode == "toys_brand") {
                $attrCode = 'toys_line';
            }
        }

        if ($attrCode ) {
            $attribute = $attrCode ? $this->helper->getAttribute($attrCode) : null;
            if ($attribute) {
                $data['attribute'] = [
                    'code' => $attrCode,
                    'label' => $attribute->getFrontendLabel()
                ];

                $customAttributesColl = $this->customAttributesFactory->create()
                ->getCollection()
                ->addFieldToFilter('attribute_code', $attrCode)
                ->addFieldToFilter($entityCode, $entityVal);
                if ($customAttributesColl->getSize()) {
                    foreach($customAttributesColl as $customAttributes) {
                        $data['data'][] = [
                            'id' => $customAttributes->getAttributeValue(),
                            'text' => $customAttributes->getAttributeValue()
                        ];
                    }
                } else {
                    $options = $attribute->getOptions();
                    if ($options) {
                        foreach($options as $option) {
                            if ($option->getLabel() && $option->getValue()) {
                                $data['data'][] = [
                                    'id' => $option->getLabel(),
                                    'text' => $option->getLabel()
                                ];
                            }
                        }
                    }
                }
            }
        }

        // $entity = $this->getRequest()->getParam('entity') ?: null;
        // if ($entity) {
        //     $attrCode = '';
        //     if ($entity == "action_toys") {
        //         $attrCode = 'toys_brand';
        //     }
        //     if ($entity == "anime") {
        //         $attrCode = 'anime_brand';
        //     }
        //     if ($entity == "card_game") {
        //         $attrCode = 'card_game';
        //     }
        //     if ($entity == "miniature_game") {
        //         $attrCode = 'miniature_game';
        //     }
        //     if ($entity == "living_card_game") {
        //         $attrCode = 'living_card_game';
        //     }
        //     if ($entity == "sport_card") {
        //         $attrCode = 'sc_sports_categories';
        //     }
        //     if ($entity == "non_sport_card") {
        //         $attrCode = 'nsc_genre';
        //     }
        //     if ($entity == "bricks_brand") {
        //         $attrCode = 'bricks_brand';
        //     }
        //     if ($entity == "dolls") {
        //         $attrCode = 'dolls_brand';
        //     }
        //     if ($entity == "vehicles") {
        //         $attrCode = 'vehicle_brand';
        //     }
        //     if ($entity == "railway") {
        //         $attrCode = 'railway_brand';
        //     }
           

        //     // LEGO
        //     // if ($entity == "bricks_brand") {
        //     //     $data['attribute'] = [
        //     //         'code' => 'lego',
        //     //         'label' => 'LEGO'
        //     //     ];

        //     //     $data['data'] = [
        //     //         [
        //     //             'value' => 'lego_sets',
        //     //             'label' => __('LEGO Complete Sets & Packs')
        //     //         ],
        //     //         [
                    
        //     //             'value' => 'minifigures',
        //     //             'label' => __('LEGO Minifigures')
        //     //         ]
        //     //     ];
        //     // }
        // }

        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}