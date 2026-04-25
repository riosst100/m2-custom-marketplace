<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\SetsRelease;

use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;

class CardGamesReleaseGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
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

        $this->collection->getSelect()
        ->join(
			['sealedproducts' => 'marketplace_card_games_sealed_products'], // Replace with the actual table name
			'sealedproducts.card_release = main_table.card_release',
			[]
		)->group('main_table.card_release');

        $this->collection->addFilterToMap('card_game', 'main_table.card_game');
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        // Check is sealed products / singles
        // $isSingles = $this->request->getParam('is_singles');
        // if (!$isSingles) {
        //     $this->collection = $this->collection->addFieldToFilter('sc_set_type', ['eq' => 'Base Set']);
        // }

        // $this->collection->getSelect()
        // ->joinLeft(
		// 	['singles_table' => 'marketplace_card_games_sets_card'], // Replace with the actual table name
		// 	'singles_table.set_id = main_table.entity_id',
		// 	['number_of_uploaded_cards' => new \Zend_Db_Expr('COUNT(singles_table.entity_id)')]
		// );
		// $this->collection->addExpressionFieldToSelect(
		// 	'number_of_uploaded_cards',
		// 	'(SELECT COUNT(*) FROM marketplace_card_games_sets_card WHERE marketplace_card_games_sets_card.set_id = main_table.entity_id)',
        //     'number_of_uploaded_cards'
		// );

        // $this->collection->getSelect()->group(['main_table.card_release']);

        $this->collection->addFieldToFilter('main_table.card_release', ['neq' => null]);

		// ->group('main_table.card_id');

        // marketplace_card_games_sets_card
        
        $this->collection->setOrder('main_table.release_year','DESC');
        $this->collection->setOrder('main_table.card_release','ASC');
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
                    $this->filterBuilder->setField('main_table.'.$paramName)->setValue($paramValue)->setConditionType('eq')->create()
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
