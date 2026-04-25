<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Ui\DataProvider;

use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;

class CardGamesVariationsSetsGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider 
{
    protected $helper;
    protected $mappingHelper;
    protected $productCollection;


    /**
     * Product link collection
     *
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory
     */
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

        // $cardGame = $this->request->getParam('card_game');
        // if (!$cardGame) {
        //     $this->collection->addFieldToFilter('card_game', ['null' => true]);
        // } else {
            // $isSingles = $this->request->getParam('is_singles');
            // if ($isSingles) {
        $this->collection->getSelect()
        ->join(
            ['singles_table' => 'marketplace_card_games_sets_card'], // Replace with the actual table name
            'singles_table.set_id = main_table.entity_id',
            []
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->productCollection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);

        $sellerId = $this->helper->getSellerId();

        $namespace = $this->request->getParam('namespace');

        $cardSet = [];
        $cardRelease = [];

        $card_product_type = $this->mappingHelper->getOptionId('card_product_type', 'Singles');

        $sellerProductCollection = $this->productCollection->create()
        ->addAttributeToSelect('mtg_card_set')
        ->addAttributeToFilter('seller_id', $sellerId)
        ->addAttributeToFilter('card_product_type', $card_product_type)
        ->addAttributeToFilter('mtg_card_set', ['notnull' => true])
        ->addFieldToFilter('type_id', 'simple');
        // dd($namespace);

        $sellerProductCollection->getSelect()->group('at_mtg_card_set.value');
        if ($sellerProductCollection->getSize()) {
            foreach($sellerProductCollection as $product) {
                if ($namespace && str_contains($namespace, 'sets') && !str_contains($namespace, 'release')) {
                    $cardSet[] = $this->mappingHelper->getOptionLabelById('mtg_card_set', $product->getData('mtg_card_set'));
                }

                if ($namespace && !str_contains($namespace, 'sets') && str_contains($namespace, 'release')) {
                    $cardRelease[] = $this->mappingHelper->getOptionLabelById('mtg_card_release', $product->getData('mtg_card_release'));
                }
            }
        }

        // dd($cardSet);
        
        if ($cardSet) {
            $this->collection->addFieldToFilter('main_table.card_set', ['in' => $cardSet]);
        } elseif ($cardRelease) {
            $this->collection->addFieldToFilter('main_table.card_release', ['in' => $cardRelease]);
        } else {
            $this->collection->addFieldToFilter('main_table.card_release', ['in' => []]);
        }

        $this->collection->getSelect()->group('main_table.entity_id');
        
            // }

            // $card_release = $this->request->getParam('card_release');
            // if ($card_release) {
            //     // $this->collection = $this->collection->addFieldToFilter('card_release', ['eq' => 'card_set']);
            //     $this->collection->getSelect()->where('main_table.card_release="'.$card_release.'"');
            // }

            // dd($this->collection->getSelect()->__toString());
            $this->collection->setOrder('main_table.card_set','ASC');
            $this->collection->addFilterToMap('card_release', 'main_table.card_release');
            $this->collection->addFilterToMap('card_series', 'main_table.card_series');
            $this->collection->addFilterToMap('release_year', 'main_table.release_year');
            $this->collection->addFilterToMap('release_type', 'main_table.release_type');
        // }
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
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

        // dd($items);

        $newItems = [];

        foreach($items as $item) {
            $item['all_images'] = $item['images'];
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