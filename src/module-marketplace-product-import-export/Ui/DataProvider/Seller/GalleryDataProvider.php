<?php

namespace TCGCollective\MarketplaceProductImportExport\Ui\DataProvider\Seller;

use TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\SellerImages\CollectionFactory;
// use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;

class GalleryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $sellerHelper;
    protected $mappingHelper;


    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * SellerDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->request = $request;
        $this->filterBuilder = $filterBuilder;

        $this->prepareUpdateUrl();

        // $categoryEntityTable = $this->collection->getTable('catalog_category_entity_varchar');
        // $this->collection->getSelect()->joinLeft(
        //     ['cce' => $categoryEntityTable],
        //     'main_table.category_id = cce.entity_id',
        //     ['categories' => 'cce.value'] // Retrieve the category name
        // );
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        
        // $cardGameAttrId = $this->request->getParam('card_game_attribute_id');
        // if ($cardGameAttrId) {
        //     $this->collection = $this->collection->addFieldToFilter('card_game', $cardGameAttrId);
        // }

        // $cardGame = $this->request->getParam('card_game');
        // if ($cardGame) {
        //     $this->collection = $this->collection->addFieldToFilter('card_game', $cardGame);
        // }

        // $drop = $this->request->getParam('secret_lair_drop');
        // if ($drop) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
            $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);

            // $filters = $this->request->getParam('filters') ?? [];
            // if (!isset($filters['category_id'])) {
            //     $categoryIds = [];
                
            //     $category = $this->mappingHelper->getCategoryByUrlKey('games');
            //     if ($category) {
            //         $children = $category->getChildrenCategories();
            //         if ($children) {
            //             foreach($children as $childCategory) {
            //                 $categoryIds[] = (int)$childCategory->getId();
            //             }
            //         }
            //     }

            //     $this->collection->addFiel(['in' => $categoryIds]);
            // }
    
            $sellerId = $this->sellerHelper->getSellerId();

            $this->collection = $this->collection->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        // }
        // Join with catalog_category_entity to get category names
        

        $this->collection->getSelect()->group(['main_table.entity_id']);
        $this->collection->setOrder('created_at','DESC');
        return $this->collection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $items = $this->getCollection()->toArray();
        $items = array_values($items['items']);

        $newItems = [];

        foreach($items as $item) {
            // $item['all_images'] = $item['images'];
            $newItems[] = $item;
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $newItems,
        ];
    }

    /**
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }

        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
                $this->addFilter(
                    $this->filterBuilder->setField($paramName)->setValue($paramValue)->setConditionType('eq')->create()
                );
            }
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
