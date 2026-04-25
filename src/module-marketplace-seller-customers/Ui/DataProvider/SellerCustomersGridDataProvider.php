<?php

namespace TCGCollective\MarketplaceSellerCustomers\Ui\DataProvider;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class SellerCustomersGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $addFieldStrategies;
    protected $addFilterStrategies;
    protected $request;
    protected $filterBuilder;
    protected $sellerFactory;
    protected $session;
    protected $currentSeller = null;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Customer\Model\Session $customerSession,
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
        $this->sellerFactory = $sellerFactory;
        $this->session = $customerSession;

        // $this->collection->getSelect()
        // ->joinLeft(
		// 	['so' => 'sales_order'], // Replace with the actual table name
		// 	'so.customer_id = e.entity_id',
		// 	['transaction_count' => new \Zend_Db_Expr('COUNT(so.entity_id)')]
		// )->where('lof.seller_id = ' . $this->getSellerId());

        $this->collection->getSelect()
        ->join(
            ['so' => 'sales_order'], // Alias for sales_order table
            'so.customer_id = e.entity_id', // Join condition (customer to orders)
            [] // Columns to select from sales_order (empty because we only need the join)
        )
        ->join(
            ['soi' => 'sales_order_item'], // Alias for sales_order_item table
            'soi.order_id = so.entity_id AND soi.lof_seller_id = ' . $this->getSellerId(), // Join condition (orders to items and filter by seller_id)
            [] // Columns to select from sales_order_item (empty because we only need the join)
        )
        ->columns(['transaction_count' => new \Zend_Db_Expr('COUNT(DISTINCT so.entity_id)')]) // Add total orders count
        ->group('e.entity_id'); // Group by customer ID
    }

    /**
     * @return bool|int|mixed
     */
    public function getSellerId()
    {
        if ($this->session->isLoggedIn()) {
            $seller = $this->getSeller();
            $sellerId = $seller ? $seller->getSellerId() : 0;
            return $sellerId;
        }

        return false;
    }

    /**
     * Get Seller by customer
     *
     * @return Object
     */
    public function getSeller()
    {
        if (!$this->currentSeller && $this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $this->currentSeller = $this->sellerFactory->create()->load($customerId, 'customer_id');
        }
        return $this->currentSeller;
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

        // $isSingles = $this->request->getParam('is_singles');
        // if ($isSingles != 1) {
        //     $this->collection = $this->collection->addFieldToFilter('parent_set_code', ['eq' => null]);
        // }

        
		// $this->collection->addExpressionFieldToSelect(
		// 	'number_of_uploaded_cards',
		// 	'(SELECT COUNT(*) FROM marketplace_card_games_sets_card WHERE marketplace_card_games_sets_card.set_id = main_table.entity_id)',
        //     'number_of_uploaded_cards'
		// );

        // $this->collection->addExpressionFieldToSelect(
        //     'main_table.card_set',
        //     'IF(main_table.secret_lair_card_set IS NOT NULL AND main_table.secret_lair_card_set != \'\', main_table.card_set, NULL)',
        //     'main_table.card_set'
        // );

        // $this->collection->getSelect()->group(['main_table.card_set', 'main_table.language']);

		// ->group('main_table.card_id');

        // marketplace_card_games_sets_card
        
        // $this->collection->setOrder('release_year','DESC');
        // $this->collection->setOrder('card_set','ASC');
        
        // $this->collection->getSelect()->order(
        //     new \Zend_Db_Expr(
        //         'CASE 
        //             WHEN main_table.card_number REGEXP "^[0-9]+$" THEN 1
        //             ELSE 2 
        //         END, 
        //         CAST(REGEXP_SUBSTR(main_table.card_number, "[0-9]+") AS UNSIGNED) ASC'
        //     )
        // );

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

        // dd($items[30]);

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $items,
        ];
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
