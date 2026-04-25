<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class Products extends \Magento\CatalogGraphQl\Model\Resolver\Products
{
    /**
     * @var ProductQueryInterface
     */
    private $searchQuery;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchApiCriteriaBuilder;

    /**
     * @param ProductQueryInterface $searchQuery
     * @param SearchCriteriaBuilder|null $searchApiCriteriaBuilder
     */
    public function __construct(
        ProductQueryInterface $searchQuery,
        SearchCriteriaBuilder $searchApiCriteriaBuilder = null
    ) {
        $this->searchQuery = $searchQuery;
        $this->searchApiCriteriaBuilder = $searchApiCriteriaBuilder ??
            \Magento\Framework\App\ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);

        parent::__construct(
            $searchQuery,
            $searchApiCriteriaBuilder
        );
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        // if (isset($args['filters']['category_uid'])) {
        //     $filterCategoryUid = $args['filters']['category_uid']['eq'];
        //     // die('filterCategoryUid'.$filterCategoryUid);
        //     if ($filterCategoryUid && in_array($filterCategoryUid, ['NzM5'])) {
        //         $rootCategoryUid = 'Mg==';
        //         $args['filters']['category_uid']['eq'] = $rootCategoryUid;
        //     }
        // }
        
        
// dd($args);        

        $this->validateInput($args);

        $searchResult = $this->searchQuery->getResult($args, $info, $context);

        if ($searchResult->getCurrentPage() > $searchResult->getTotalPages() && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResult->getCurrentPage(), $searchResult->getTotalPages()]
                )
            );
        }

        $newArgs = $args;

        // unset($args['filter']['card_product_type']);
        if (!empty($newArgs['filter']) && is_array($newArgs['filter'])) {
            // Ambil elemen pertama
            $first = array_slice($newArgs['filter'], 0, 1, true);

            // Replace seluruh array dengan hanya elemen pertama
            $newArgs['filter'] = $first;
        }

        $this->validateInput($newArgs);

        $searchResult2 = $this->searchQuery->getResult($newArgs, $info, $context);

        if ($searchResult2->getCurrentPage() > $searchResult2->getTotalPages() && $searchResult2->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResult2->getCurrentPage(), $searchResult2->getTotalPages()]
                )
            );
        }

        $argsWithoutFilter = $args;
        $argsWithoutFilter['filter'] = [];

        $this->validateInput($argsWithoutFilter);

        // dd($argsWithoutFilter);

        $searchResultWithoutFilter = $this->searchQuery->getResult($argsWithoutFilter, $info, $context);

        if ($searchResultWithoutFilter->getCurrentPage() > $searchResultWithoutFilter->getTotalPages() && $searchResultWithoutFilter->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResultWithoutFilter->getCurrentPage(), $searchResultWithoutFilter->getTotalPages()]
                )
            );
        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getProductsSearchResult(),
            'suggestions' => $searchResult->getSuggestions(),
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurrentPage(),
                'total_pages' => $searchResult->getTotalPages()
            ],
            'args' => $args,
            'search_result' => $searchResult,
            'search_result2' => $searchResult2,
            'search_result_without_filter' => $searchResultWithoutFilter,
            'layer_type' => isset($args['search']) ? Resolver::CATALOG_LAYER_SEARCH : Resolver::CATALOG_LAYER_CATEGORY,
            'category_uid' => $args['category_uid'] ?? null
        ];

        if (isset($args['filter']['category_id'])) {
            $data['categories'] = $args['filter']['category_id']['eq'] ?? $args['filter']['category_id']['in'];
            $data['categories'] = is_array($data['categories']) ? $data['categories'] : [$data['categories']];
        }

        // dd($data);

        return $data;
    }

    /**
     * Validate input arguments
     *
     * @param array $args
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     */
    private function validateInput(array $args)
    {
        if (isset($args['searchAllowed']) && $args['searchAllowed'] === false) {
            throw new GraphQlAuthorizationException(__('Product search has been disabled.'));
        }
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args['search']) && !isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        }
    }
}
