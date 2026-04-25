<?php

namespace TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSetsCard\Grid;

use TCGCollective\ProductAttributesLink\Ui\DataProvider\CardGamesCardGridDataProvider;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = CardGamesCardGridDataProvider::class;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => 
        ['set_id' => 'main_table.set_id'],
        'card_game' => 'sets.card_game'
        ];

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'marketplace_card_games_sets_card',
        $resourceModel = \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSetsCard::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    protected function _initSelect() {

        parent::_initSelect();
        $this->getSelect()->joinLeft(
                ['sets' => $this->getTable('marketplace_card_games_sets')], 
                'sets.entity_id = main_table.set_id',
                ['card_game']
        );
        return $this;
    }
    protected function _initInitialFieldsToSelect() {
        parent::_initInitialFieldsToSelect();
    }

}