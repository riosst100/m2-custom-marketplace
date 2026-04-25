<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper\NodeWrapperFactory;
use TCGCollective\MarketplaceGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper\Node;

/**
 * Data extractor for category tree processing in GraphQL resolvers.
 */
class ExtractDataFromCategoryTree extends \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree 
{
    /**
     * @var NodeWrapperFactory
     */
    private $nodeWrapperFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\CatalogGraphQl\Model\Category\Hydrator
     */
    private $hydrator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @param NodeWrapperFactory $nodeWrapperFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\CatalogGraphQl\Model\Category\Hydrator $hydrator
     * @param \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface
     */
    public function __construct(
        NodeWrapperFactory $nodeWrapperFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\CatalogGraphQl\Model\Category\Hydrator $hydrator,
        \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface
    ) {
        $this->nodeWrapperFactory = $nodeWrapperFactory;
        $this->categoryFactory = $categoryFactory;
        $this->hydrator = $hydrator;
        $this->storeManagerInterface = $StoreManagerInterface;
        parent::__construct($nodeWrapperFactory);
    }

    public function getRootCategoryId()
    {
        return $this->storeManagerInterface->getStore()->getRootCategoryId();
    }

    /**
     * Build result tree from collection
     *
     * @param Collection $collection
     * @param array $topLevelCategoryIds
     * @return array
     */
    public function buildTree(Collection $collection, array $topLevelCategoryIds) : array
    {
        $rootCategoryId = $this->getRootCategoryId();

        $wrapper = $this->nodeWrapperFactory->create();
        /** @var Category $item */
        foreach ($collection->getItems() as $item) {
            $wrapper->wrap($item);
        }
        $tree = [];
        foreach ($topLevelCategoryIds as $topLevelCategory) {
            $node = $wrapper->getNodeById($topLevelCategory);
            if ($node) {
                $category = $this->categoryFactory->create()->load($topLevelCategory);
                $parentCategory = $category->getParentCategory();
                $parentCategoryId = (int)$parentCategory->getId();

                if ($parentCategoryId != $rootCategoryId) {
                    $parentNode = new Node($parentCategoryId);
                    $parentNode->setModelData(
                        $this->hydrator->hydrateCategory($parentCategory)
                    );

                    // if ($category->getName() == "Bricks") {
                    // dd($category->getUrlPath());
                    // }

                    $node->addParent($parentNode);
                }

                $tree[] = $node->renderArray();
            }
        }

        foreach ($tree[0]['children'] as $key => $category) {
            if (isset($category['name']) && $category['name'] === "Bricks") {
                // $category['path'] = $newPath;
                // return true;
                // dd($category['url_path']);
                if ($category['url_path'] == "bricks") {
                    $category['url_path'] = 'bricks/categories';
                }
            }

            $tree[0]['children'][$key] = $category;

            // if (isset($category['children']) && is_array($category['children'])) {
            //     if (updateCategoryPath($category['children'], $categoryName, $newPath)) {
            //         return true;
            //     }
            // }
        }
        
        return $this->sortTree($tree);
    }

    /**
     * Recursive method to sort tree
     *
     * @param array $tree
     * @return array
     */
    private function sortTree(array &$tree): array
    {
        foreach ($tree as &$node) {
            if (!empty($node['children'])) {
                uasort($node['children'], function ($element1, $element2) {
                    return ($element1['position'] <=> $element2['position']);
                });
                $node['children'] = $this->sortTree($node['children']);
                if (isset($node['children_count'])) {
                    $node['children_count'] = count($node['children']);
                }
            } elseif (isset($node['children_count'])) {
                $node['children_count'] = 0;
            }
        }

        return $tree;
    }
}
