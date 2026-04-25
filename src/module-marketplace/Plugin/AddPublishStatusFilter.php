<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

class AddPublishStatusFilter
{
    /**
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly FilterBuilder $filterBuilder,
        private readonly FilterGroupBuilder $filterGroupBuilder
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function afterBuild(
        \Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder $subject,
        SearchCriteriaInterface $searchCriteria
    ) {
        $this->addFilter($searchCriteria, 'publish_status', '1', 'eq');

        return $searchCriteria;
    }
    
    private function addFilter(
        SearchCriteriaInterface $searchCriteria,
        string $field,
        $value,
        ?string $condition = null
    ): void {
        $filter = $this->filterBuilder
            ->setField($field)
            ->setValue($value)
            ->setConditionType($condition)
            ->create();

        $this->filterGroupBuilder->addFilter($filter);
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->create();
        $searchCriteria->setFilterGroups($filterGroups);
    }
}
