<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MappingData;

use Magento\Framework\App\Action\Context;

class Fields extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;
    protected $cardGamesMappingCollFactory;


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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /* Create a new product object */
        $this->customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $this->cardGamesMappingCollFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory::class);

        $attributeCode = $this->getRequest()->getParam('code');

        $fields = [];

        try {
            $collection = $this->cardGamesMappingCollFactory->create()
            ->getCollection()
            ->setPageSize(1);
            if ($collection->getSize()) {
                foreach($collection->getFirstItem()->getData() as $field => $value) {
                    $fields[] = $field;
                }
            }

            $collection = $this->prdAttrLinkCollFactory->create()
            ->addFieldToFilter('attribute_code', $attributeCode);
            if ($collection->getSize()) {

            }
        } catch (\Exception $e) {}

        $resultJson->setData(['success' => false, 'data' => $data]);
        return $resultJson;
    }
}