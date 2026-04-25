<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\SidebarFilterAttributes\Grid;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Collection to present grid of customers on admin area
 */
class Collection extends SearchResult
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => ['entity_id' => 'main_table.entity_id']];

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param ResolverInterface $localeResolver
     * @param string $mainTable
     * @param string $resourceModel
     * @param TimezoneInterface|null $timeZone
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        ResolverInterface $localeResolver,
        $mainTable = 'eav_attribute',
        $resourceModel = Attribute::class,
        TimezoneInterface $timeZone = null
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->timeZone = $timeZone ?: ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            ['cea' => 'catalog_eav_attribute'],
            'cea.attribute_id = main_table.attribute_id',
            ['position','is_master_attribute']
        )
        ->where('main_table.entity_type_id = 4 AND cea.is_visible = 1 AND cea.is_filterable = 1')
        ->order('cea.position DESC')
        ->order('main_table.attribute_code ASC')
        ->group('main_table.attribute_id');

        $this->addFilterToMap('position', 'cea.position');
        $this->addFilterToMap('is_master_attribute', 'cea.is_master_attribute');
        // $this->collection->addFieldToFilter('entity_type_id', 4);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_string($field) && count(explode('.', $field)) === 1) {
            if ($field == 'is_master_attribute') {
                $field = 'cea.' . $field;
            } else {
                $field = 'main_table.' . $field;
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add fulltext filter
     *
     * @param string $value
     * @return $this
     */
    public function addFullTextFilter(string $value)
    {
        $fields = $this->getFulltextIndexColumns();
        $whereCondition = '';
        foreach ($fields as $key => $field) {
            $field = 'main_table.' . $field;
            $condition = $this->_getConditionSql(
                $this->getConnection()->quoteIdentifier($field),
                ['like' => "%$value%"]
            );
            $whereCondition .= ($key === 0 ? '' : ' OR ') . $condition;
        }

        if ($whereCondition) {
            $this->getSelect()->where($whereCondition);
        }

        return $this;
    }

    /**
     * Returns list of columns from fulltext index
     *
     * @return array
     */
    private function getFulltextIndexColumns(): array
    {
        $indexes = $this->getConnection()->getIndexList($this->getMainTable());
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                return $index['COLUMNS_LIST'];
            }
        }
        return [];
    }
}
