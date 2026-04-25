<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Product\Seller;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductDataProvider extends \Lof\MarketPlace\Ui\DataProvider\Product\Seller\ProductDataProvider
{
    protected $request;
    protected $mappingHelper;
    protected $redirect;


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
            $helper,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->redirect = $objectManager->get(\Magento\Framework\App\Response\RedirectInterface::class);

        // $categoryIds = [];


        // $categoryUrlKey = null;
        // if ($this->request->getParam('category')) {
        //     $categoryUrlKey = $this->request->getParam('category');
        // } else {
        //     $refererUrl = $this->redirect->getRefererUrl();

        //     // Parse the referer URL
        //     $parsedUrl = parse_url($refererUrl);
        //     $path = $parsedUrl['path'];

        //     // Split the path into segments
        //     $pathSegments = explode('/', trim($path, '/'));

        //     // Find the index of the 'category' segment
        //     $categoryIndex = array_search('category', $pathSegments);

        //     // Check if the category segment exists and if there's another segment after it
        //     if ($categoryIndex !== false && isset($pathSegments[$categoryIndex + 1])) {
        //         $categoryUrlKey = $pathSegments[$categoryIndex + 1]; // Get the category
        //     } else {
        //         $categoryUrlKey = null; // Handle cases where category is not found
        //     }
        // }

        // if ($categoryUrlKey) {
        //     $category = $this->mappingHelper->getCategoryByUrlKey($categoryUrlKey);
        //     if ($category) {
        //         $children = $category->getChildrenCategories();
        //         if ($children) {
        //             foreach($children as $childCategory) {
        //                 $categoryIds[] = (int)$childCategory->getId();
        //             }
        //         }
        //     }
        // }

        // This line should be removed or commented out during debugging
        // $categoryIds = [12,42,27,754,512];

        // Debugging: Log the category IDs before applying the filter
        // dd('Final category IDs: ', $categoryIds);

        // $this->collection->addCategoriesFilter(['in' => $categoryIds]);
        // Log the SQL query for debugging
        // dd($this->collection->getSelect()->__toString());

        // dd($this->collection->getSelect()->__toString());


        // if ($helper->getSellerId()) {
        //     $this->collection->addAttributeToFilter('seller_id', $helper->getSellerId());
        // } else {
        //     $this->collection->addAttributeToFilter('seller_id', 0);
        // }

        $customTable = $this->collection->getTable('catalog_product_entity_int');

        // Join the `catalog_product_entity_int` table
        // $this->collection->getSelect()->joinLeft(
        //     ['cpei' => $customTable],
        //     'e.entity_id = cpei.entity_id AND cpei.attribute_id = 1304', // Ensure attribute_id matches
        //     [
        //         'value AS custom_value',
        //         'is_published' => new \Zend_Db_Expr("IF(cpei.value = 2, 0, 1)")
        //     ]
        // );
        // dd('ok');

        $this->collection->getSelect()->joinLeft(
            ['cpei' => $customTable],
            'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
            [
                'value AS custom_value',
                'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
            ]
        );
        // Add a filter to check if `value` is 1
        // $this->collection->getSelect()->where('cpei.value = ?', 1);

        // $attributeId = 1304; // Replace with the desired attribute ID
        // $this->collection->getSelect()->where('cpei.attribute_id = ?', $attributeId);

        $this->collection->getSelect()->group('e.entity_id');

        // dd($this->collection->getSelect()->__toString());

        // if ( $this->collection->getSize()) {
        //     foreach( $this->collection as $model) {
        //         // if ($model->getId() == 235) {
        //             // dd($model->getCustomValue());
        //         // }
        //     }
        // }
        
        $this->collection->getSelect()->order('entity_id desc');
        // dd($this->collection->getSize());
        /*Join with vendor table.*/
    }
}
