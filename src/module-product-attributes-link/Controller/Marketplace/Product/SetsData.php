<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;

class SetsData extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;
    protected $cardGameSets;


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
        // $defaultAttributeSetId = $defaultAttributeSetId != 1 ? $defaultAttributeSetId  => self::DEFAULT_ATTRIBUTE_SET_ID;

        $data = [];

        $data['grid_data'] = [];
        $data['grid_column'] = [];

        $columnMapping = [
            'images' => __('Images'),
            'release_year' => __('Release Year'),
            'release_type' => __('Release Type'),
            'card_set_abbreviation' => __('Set Abbreviation'),
            'card_set' => __('Set Name')
        ];

        $columnFields = array_keys($columnMapping);
        $columnFieldsLabel = array_values($columnMapping);

        // dd($columnFieldsLabel);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // /* Create a new product object */
        // $this->customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $this->cardGameSets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory::class);
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $collection = $this->cardGameSets->create()
        ->getCollection()
        ->addFieldToFilter('card_game', '3944');
        if ($collection->getSize()) {
            foreach($collection as $model) {
                $gridData = [];
                foreach($model->getData() as $field => $fieldData) {
                    if (in_array($field, $columnFields)) {
                        if ($field == "images") {
                            $cardImageUrlsArr = explode(",", $fieldData);
                            if (count($cardImageUrlsArr)) {
                                $fieldData = $cardImageUrlsArr[0];
                            }

                            if (!$fieldData) {
                                $fieldData = 'singles/placeholder.jpg';
                            }

                            $fieldData = $mediaUrl.'.thumbs/'.$fieldData;
                        }
                        $gridData[$field] = $fieldData;
                    }
                }

                $gridData = array_replace(array_flip($columnFields), $gridData);

                $data['grid_data'][] = $gridData;
            }
        }

        

        $data['grid_column'] = $columnFieldsLabel;

        $resultJson->setData(['success' => true, 'data' => $data]);
        return $resultJson;
    }
}