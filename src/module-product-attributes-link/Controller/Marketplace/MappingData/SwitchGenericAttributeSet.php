<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MappingData;

use Magento\Framework\App\Action\Context;

class SwitchGenericAttributeSet extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;
    protected $cardGamesMappingCollFactory;
    protected $mappingHelper;


    const DEFAULT_ATTRIBUTE_SET_ID = 4;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory
     */
    protected $prdAttrLinkCollFactory;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->prdAttrLinkCollFactory = $prdAttrLinkCollFactory;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
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

        $defaultAttributeSetId = (int)$this->helper->getConfig("seller_settings/default_attribute_set_id");
        $defaultAttributeSetId = $defaultAttributeSetId != 1 ? $defaultAttributeSetId : self::DEFAULT_ATTRIBUTE_SET_ID;

        $data = [
            'attribute_set_id' => null,
            'mapping_group_name' => null,
            'mapping_priority' => 0
        ];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $this->cardGamesMappingCollFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory::class);
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        $lastMappingAttribute = $this->customerSession->getLastMappingAttribute();
        $lastMappingValue = $this->customerSession->getLastMappingValue();
        if ($lastMappingAttribute) {
        }

        $mapping = null;

        $mappingPriority = $this->getRequest()->getParam('mapping_priority') ?: 0;
        $attributeCode = $this->getRequest()->getParam('code');

        $selectedOptionId = $this->getRequest()->getParam('option_id');
        $selectedGroupName = $this->getRequest()->getParam('mapping_group_name');

        $selectedCategoryId = $this->getRequest()->getParam('category_id');

        // dd($attributeCode);

        // $data = $this->mappingHelper->getAttributeSetData($attributeCode, $mappingPriority, $selectedOptionId, $selectedGroupId, $selectedCategoryId);
        $data = $this->mappingHelper->getAttributeSetData($attributeCode, $mappingPriority, $selectedOptionId, null, null, null, $selectedGroupName);
        if ($data) {
            $data['last_priority'] = false;

            $higherPriority = $this->mappingHelper->getHigherPriorityAttributeSetMapping(
                $data
            );

            if (!$higherPriority) {
                $data['last_priority'] = true;
            }
        }

        $resultJson->setData(['success' => true, 'data' => $data]);
        return $resultJson;
    }

    public function getOptionLabelById($attributeCode, $optionId) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

        $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        $optionlabel =  $attribute->getSource()->getOptionText($optionId);

        return $optionlabel;
    }
}