<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MappingData;

use Magento\Framework\App\Action\Context;

class GetCustomTableOptions extends \Magento\Framework\App\Action\Action
{
    protected $jsonResultFactory;


    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $cardGameSets;
    protected $legoSets;
    protected $legoSingles;
    protected $sportCardsSets;
    protected $sportCardsSetsCard;
    protected $nonSportCardsSets;
    protected $nonSportCardsSetsCard;
    protected $miniatureGameSets;
    protected $miniatureGameSingles;
    protected $livingCardGameSets;
    protected $cardGameSetsCard;
    protected $_urlBuilder;

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
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSingles,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory $sportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory $miniatureGameSingles,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        $this->cardGameSets = $cardGameSets;
        $this->cardGameSetsCard = $cardGameSetsCard;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->legoSingles = $legoSingles;
        $this->sportCardsSets = $sportCardsSets;
        $this->sportCardsSetsCard = $sportCardsSetsCard;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->nonSportCardsSetsCard = $nonSportCardsSetsCard;
        $this->miniatureGameSets = $miniatureGameSets;
        $this->miniatureGameSingles = $miniatureGameSingles;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct(
            $context
        );
    }


    // function isCustomTablesAttributes($attrCode)
    // {
    //     $wildcardAttributes = ['card_set','card_name'];
    //     foreach($wildcardAttributes as $wildcardAttrCode) {
    //         if (stripos($attrCode,$wildcardAttrCode) !== false) return true;
    //     }
    //     return false;
    // }

    public function execute()
    {
        $data = [
            'data' => []
        ];

        $result = [];

        $requestData = $this->getRequest()->getParam('data');
        // $attributeData = $this->getRequest()->getParam('attribute');
        $productData = $requestData['product'] ?? null;
        if ($productData) {
            // $categoryName = null;

            // $categoryId = $productData['category_ids'] ?? null;
            // if ($categoryId) {
            //     $category = $this->mappingHelper->getCategoryById($categoryId);
            //     if ($category) {
            //         $categoryName = $category->getName();
            //     }
            // }

            // if ($categoryName) {
            //     // CARD GAME CATEGORIES
            //     if ($categoryName == "Collectible Card Games") 
            //     {
            //         $mainAttrCode = 'card_game';
            //         $mainSetsAttrCode = 'card_set';
            //         $mainSinglesAttrCode = 'card_name';

            //         $mainAttrId = $productData[$mainAttrCode] ?? null;
            //         if ($mainAttrId) {
            //             $prefix = $this->getAttributeNamePrefix($mainAttrId, $mainAttrCode);
            //             $setsAttrCode = $prefix.$mainSetsAttrCode;
            //             $singlesAttrCode = $prefix.$mainSinglesAttrCode;
            //         }
            //     }
            // }

            // $ajaxOptions = ['card_set','card_name','artist','card_number','card_feature','card_type'];
            // foreach($productData as $attributeCode => $attributeValue) {
            //     if (array_filter($ajaxOptions, fn($opt) => str_contains($attributeCode, $opt))) {
            //         // Attribute Data
            //         $options = [];
                    
            //         $attribute = [
            //             'code' => $attributeCode,
            //             'label' => ''
            //         ];

            //         // Get Sets Data
            //         // $collection = $this->cardGameSetsCard->create()
            //         // ->getCollection()
            //         // ->addFieldToSelect(['card_name','entity_id'])
            //         // ->addFieldToFilter('set_id', $fieldValue);
            //         // $collection->getSelect()->group('card_name');
            //         // if (!$collection->getSize()) {
            //         //     $fieldValueLabel = $this->mappingHelper->getOptionLabelById($setsAttrCode, $fieldValue);
            //         //     $fieldValueLabel = addslashes($fieldValueLabel);
            //         //     $collection = $this->cardGameSetsCard->create()
            //         //     ->getCollection()
            //         //     ->addFieldToSelect(['card_name','entity_id']);
            //         //     $collection->getSelect()->joinLeft(
            //         //         ['sets' => 'marketplace_card_games_sets'],
            //         //         'main_table.set_id=sets.entity_id',
            //         //         []
            //         //     )
            //         //     ->where("sets.card_set='".$fieldValueLabel."'")
            //         //     ->group('main_table.card_name');
            //         // }

            //         // if ($collection->getSize()) {
            //             // $existingOptions = [];

            //         // $existingOptionsColl = $this->mappingHelper->getCachedAttributeOptions($attributeCode);
            //         // if ($existingOptionsColl) {
            //         //     foreach($existingOptionsColl as $optionModel) {
            //         // //         // $existingOptions[$optionModel->getLabel()] = $optionModel->getValue();
            //         //         $options[] = [
            //         //             'label' => trim($optionModel->getLabel()),
            //         //             'value' => $optionModel->getValue()
            //         //         ];
            //         //     }
            //         // }
            //         // $customTableExist = true;
            //         // foreach($collection as $model) {
            //         //     $options[] = [
            //         //         'label' => trim($model->getData('card_name')),
            //         //         'value' => $existingOptions[$model->getData('card_name')] ?? $model->getData('entity_id')
            //         //     ];
            //         // }
            //         // $selectedOtions = array_filter($existingOptionsColl, fn($o) => $o['value'] == $attributeValue);
            //         // $selectedOtions = array_values($selectedOtions);

            //         $result[] = [
            //             'attribute' => $attribute,
            //             'options' => $options
            //         ];
            //     }
            // }
            $ajaxOptions = ['card_set','card_name','artist','card_number','card_feature','card_type'];

            $filteredKeys = array_keys(array_filter(
                $productData,
                function($_, $attributeCode) use ($ajaxOptions) {
                    foreach ($ajaxOptions as $opt) {
                        if (stripos($attributeCode, $opt) !== false) {
                            return true; // ketemu, include key ini
                        }
                    }
                    return false; // tidak ada match
                },
                ARRAY_FILTER_USE_BOTH
            ));

            $result = array_map(
                function($attributeCode) {
                    return [
                        'searchUrl' => $this->_urlBuilder->getUrl(
                            'productattributeslink/mappingdata/searchoptions/attribute/'.$attributeCode
                        ),
                        'attribute' => [
                            'code' => $attributeCode,
                            'label' => ''
                        ],
                        // 'options' => []
                    ];
                },
                $filteredKeys
            );
        }

        // die('<pre>'.print_r($productData,true));

        // $categories = $this->getRequest()->getParam('categories') ?: null;
        // if ($entity == "card_game") {
        //     $options = [];
        //     $attribute = [
        //         'code' => 'set_id',
        //         'label' => 'Sets'
        //     ];
        //     $sets = $categories ? $this->mappingHelper->getCardGameSets($categories) : null;
        //     if ($sets) {
        //         foreach($sets as $model) {
        //             $options[] = [
        //                 'label' => $model->getCardSet(),
        //                 'value' => $model->getEntityId()
        //             ];
        //         }
        //     }

        //     $result[] = [
        //         'attribute' => $attribute,
        //         'options' => $options
        //     ];
        // }

        $data['data'] = $result;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$this->jsonResultFactory = $objectManager->create(\Magento\Framework\Controller\Result\JsonFactory::class);

        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}