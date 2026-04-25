<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;

class GetEntityData extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $jsonResultFactory;


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $helper;
    protected $attributeSetMappingFactory;
    protected $mainCategoriesConfigProvider;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory $attributeSetMappingFactory,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->helper = $helper;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->attributeSetMappingFactory = $attributeSetMappingFactory;
        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
    }

    public function execute()
    {
        $data = [
            'attribute' => [],
            'data' => []
        ];

        $categoryId = $this->getRequest()->getParam('entity') ?: null;
        if ($categoryId) {

            $attrCode = null;

            $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByCategoryId($categoryId);
            if ($mainCategoriesConfigData) {
                $attrCode = $mainCategoriesConfigData['mainAttrCode'] ?? null;
            }
            
            // dd($attrCode);
            $attribute = $attrCode ? $this->helper->getAttribute($attrCode) : null;
            if ($attribute) {
                $data['attribute'] = [
                    'code' => $attrCode,
                    'label' => $attribute->getFrontendLabel()
                ];
                $cardGameOptions = $attribute->getOptions();
                if ($cardGameOptions) {
                    foreach($cardGameOptions as $option) {
                        if (!$this->isAttributeSetMappingExisst($attrCode, $option->getValue())) {
                            continue;
                        }

                        if ($option->getLabel() && $option->getValue()) {
                            $data['data'][] = [
                                'value' => $option->getValue(),
                                'label' => $option->getLabel()
                            ];
                        }
                    }
                }
            }
        }

        usort($data['data'], fn($a, $b) => $a['label'] <=> $b['label']);

        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }

    public function isAttributeSetMappingExisst($attrCode, $attrVal) 
    {
        $collection = $this->attributeSetMappingFactory->create()
        ->getCollection();
        $collection->addFieldToFilter('attribute_code', $attrCode);
        $collection->addFieldToFilter('attribute_option_id', $attrVal);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }
}