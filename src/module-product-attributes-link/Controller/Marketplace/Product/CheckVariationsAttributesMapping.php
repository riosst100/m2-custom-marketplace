<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;

class CheckVariationsAttributesMapping extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $variationsAttributesMapping;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketplaceConfigurableProduct\Model\VariationsAttributesMappingFactory $variationsAttributesMapping
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        $this->variationsAttributesMapping = $variationsAttributesMapping;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        
        // $productTypeVal = $this->getRequest()->getParam('val');

        // $entityCode = $this->getRequest()->getParam('entity_code');
        // $entityVal = $this->getRequest()->getParam('entity_val');

        $data = [
            'is_variations_attributes_exist' => false
        ];

        // $productType = $this->getRequest()->getParam('product_type');
        // $source = $this->getRequest()->getParam('source');
        // // dd($source);
        // if ($productType == "configurable" || $source != "database") {
        //     $data['is_custom_table_exist'] = false;
        //     $data['is_singles'] = false;
        // } else {
        //     $data = $this->mappingHelper->getCustomTableDetails($productTypeCode, $productTypeVal, $entityCode, $entityVal, $productType);
        // }
        $product = $this->getRequest()->getParam('product');
        if ($product) {
            $attributeSetId = $product['attribute_set_id'] ?? null;

            $collection = $this->variationsAttributesMapping->create()
            ->getCollection();
            
            $collection->addFieldToFilter('attribute_set_id', $attributeSetId);

            if ($collection->getSize()) {
                $data['is_variations_attributes_exist'] = true;
            }
        }

        $resultJson->setData([
            'success' => true,
            'data' => $data
        ]);

        return $resultJson;
    }
}