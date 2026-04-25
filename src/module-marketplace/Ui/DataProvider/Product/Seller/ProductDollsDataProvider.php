<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Product\Seller;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductDollsDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    protected $mappingHelper;
    protected $request;


//     /**
//      * Construct
//      *
//      * @param string $name
//      * @param string $primaryFieldName
//      * @param string $requestFieldName
//      * @param CollectionFactory $collectionFactory
//      * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
//      * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
//      * @param array $meta
//      * @param array $data
//      */
//     public function __construct(
//         $name,
//         $primaryFieldName,
//         $requestFieldName,
//         CollectionFactory $collectionFactory,
//         \Lof\MarketPlace\Helper\Data $helper,
//         array $addFieldStrategies = [],
//         array $addFilterStrategies = [],
//         array $meta = [],
//         array $data = []
//     ) {
//         parent::__construct(
//             $name,
//             $primaryFieldName,
//             $requestFieldName,
//             $collectionFactory,
//             $addFieldStrategies,
//             $addFilterStrategies,
//             $meta,
//             $data
//         );

//         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//         $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);

//         $categoryIds = [];
        
//         $category = $this->mappingHelper->getCategoryByUrlKey('lego');
//         if ($category) {
//             $children = $category->getChildrenCategories();
//             if ($children) {
//                 foreach($children as $childCategory) {
//                     $categoryIds[] = (int)$childCategory->getId();
//                 }
//             }
//         }

//         $this->collection->addCategoriesFilter(['in' => $categoryIds]);

//         $customTable = $this->collection->getTable('catalog_product_entity_int');
//         $this->collection->getSelect()->joinLeft(
//             ['cpei' => $customTable],
//             'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
//             [
//                 'value AS custom_value',
//                 'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
//             ]
//         );

//         $this->collection->getSelect()->group('e.entity_id');
//         $this->collection->getSelect()->order('entity_id desc');
//     }
// }
    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);

        $filters = $this->request->getParam('filters') ?? [];
        if (!isset($filters['category_id'])) {
            $categoryIds = [];
            
            $category = $this->mappingHelper->getCategoryByUrlKey('fashion-dolls');
            if ($category) {
                $children = $category->getChildrenCategories();
                if ($children) {
                    foreach($children as $childCategory) {
                        $categoryIds[] = (int)$childCategory->getId();
                    }
                }
            }

            $this->collection->addCategoriesFilter(['in' => $categoryIds]);
        }

        // $this->collection->addAttributeToSelect('card_product_type');
        // $this->collection->addAttributeToSelect('card_game');
        // $this->collection->addAttributeToSelect('living_card_game');
        // $this->collection->addAttributeToSelect('miniature_game');
        $this->collection->addAttributeToSelect('website_ids');

        // Join with catalog_category_product to get product category names
        $categoryTable = $this->collection->getTable('catalog_category_product');
        $this->collection->getSelect()->joinLeft(
            ['ccp' => $categoryTable],
            'e.entity_id = ccp.product_id',
            ['category_id' => 'ccp.category_id']
        );

        // Join with catalog_category_entity to get category names
        $categoryEntityTable = $this->collection->getTable('catalog_category_entity_varchar');
        $this->collection->getSelect()->joinLeft(
            ['cce' => $categoryEntityTable],
            'ccp.category_id = cce.entity_id',
            ['categories' => 'cce.value'] // Retrieve the category name
        );

        $customTable = $this->collection->getTable('catalog_product_entity_int');
        $this->collection->getSelect()->joinLeft(
            ['cpei' => $customTable],
            'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
            [
                'value AS custom_value',
                'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
            ]
        );

        

        $this->collection->getSelect()->group('e.entity_id');
        $this->collection->getSelect()->order('entity_id desc');
        
        $this->collection->addFilterToMap('is_published', 'cpei.value');
        if ($helper->getSellerId()) {
            $this->collection->addAttributeToFilter('seller_id', $helper->getSellerId());
        } else {
            $this->collection->addAttributeToFilter('seller_id', 0);
        }
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
