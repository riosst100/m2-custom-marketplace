<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogGraphQl\Model\Category\Filter\SearchCriteria;
use Magento\Store\Api\Data\StoreInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CatalogGraphQl\Model\Category\CategoryFilter;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Categories resolver, used for GraphQL category data request processing.
 */
class SellerCategoriesQuery implements ResolverInterface 
{
    /**
     * @var CategoryTree
     */
    private $categoryTree;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var ExtractDataFromCategoryTree
     */
    private $extractDataFromCategoryTree;

    /**
     * @var ArgumentsProcessorInterface
     */
    private $argsSelection;

    /**
     * @var SearchCriteria
     */
    private $searchCriteria;
    private $catalogHelper;

    /**
     * @param CategoryTree $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CategoryFilter $categoryFilter
     * @param ArgumentsProcessorInterface $argsSelection
     * @param SearchCriteria $searchCriteria
     */
    public function __construct(
        CategoryTree $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CategoryFilter $categoryFilter,
        ArgumentsProcessorInterface $argsSelection,
        SearchCriteria $searchCriteria,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->categoryFilter = $categoryFilter;
        $this->argsSelection = $argsSelection;
        $this->searchCriteria = $searchCriteria;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }
        $store = $context->getExtensionAttributes()->getStore();

        if (!isset($args['filters'])) {
            $args['filters']['ids'] = ['eq' => $store->getRootCategoryId()];
        }
        try {
            $processedArgs = $this->argsSelection->process($info->fieldName, $args);
            $filterResults = $this->categoryFilter->getResult($processedArgs, $store, [], $context);

            $topLevelCategoryIds = $filterResults['category_ids'];
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        $sellerUrl = $args['sellerUrl'] ?? null;
        if (!$sellerUrl) {
            throw new GraphQlInputException(__('Please provide seller url.'));
        }

        $newChildren = [];

        $sellerMainCategoryIds = $this->catalogHelper->getSellerMainCategoryIds($sellerUrl);

        $categories = $this->fetchCategoriesByTopLevelIds($topLevelCategoryIds, $info, $processedArgs, [], $context);
        foreach($categories[0]['children'] as $index => $category) {
            if (in_array($category['id'], $sellerMainCategoryIds)) {
                $newChildren[] = $category;
            }
        }

        $categories[0]['children'] = $newChildren;

        return $categories;
    }

    /**
     * Fetch category tree data
     *
     * @param array $topLevelCategoryIds
     * @param ResolveInfo $info
     * @param array $processedArgs
     * @param array $attributeNames
     * @param ContextInterface $context
     * @return array
     * @throws LocalizedException
     */
    private function fetchCategoriesByTopLevelIds(
        array $topLevelCategoryIds,
        ResolveInfo $info,
        array $processedArgs,
        array $attributeNames,
        ContextInterface $context
    ) : array {
        // pagination must be applied to top level category results, children categories are not paginated
        $processedArgs['pageSize'] = 0;
        $searchCriteria = $this->searchCriteria->buildCriteria(
            $processedArgs,
            $context->getExtensionAttributes()->getStore()
        );
        $categoryCollection = $this->categoryTree->getFlatCategoriesByRootIds(
            $info,
            $topLevelCategoryIds,
            $searchCriteria,
            $attributeNames,
            $context
        );
        return $this->extractDataFromCategoryTree->buildTree($categoryCollection, $topLevelCategoryIds);
    }
}
