<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Catalog\Model\Category;
use Magento\CatalogGraphQl\Model\Resolver\Category\CheckCategoryIsActive;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree as CategoryTreeDataProvider;

class ParentCategoryTree implements ResolverInterface
{
    /**
     * Name of type in GraphQL
     */
    public const CATEGORY_INTERFACE = 'CategoryInterface';

    /**
     * @var CategoryTreeDataProvider
     */
    private $categoryTree;

    /**
     * @var ExtractDataFromCategoryTree
     */
    private $extractDataFromCategoryTree;

    /**
     * @var CheckCategoryIsActive
     */
    private $checkCategoryIsActive;

    /**
     * @param CategoryTreeDataProvider $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param CheckCategoryIsActive $checkCategoryIsActive
     */
    public function __construct(
        CategoryTreeDataProvider $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        CheckCategoryIsActive $checkCategoryIsActive
    ) {
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->checkCategoryIsActive = $checkCategoryIsActive;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        die('ok');
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }

        $rootCategoryId = isset($args['id']) ? (int)$args['id'] :
            (int)$context->getExtensionAttributes()->getStore()->getRootCategoryId();

        if ($rootCategoryId !== Category::TREE_ROOT_ID) {
            $this->checkCategoryIsActive->execute($rootCategoryId);
        }
        
//         $categoryId = 5;
// $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
// $object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
// echo "<pre>";
// print_r($object_manager->getData());

        $store = $context->getExtensionAttributes()->getStore();
        $categoriesTree = $this->categoryTree->getTreeCollection($info, $rootCategoryId, (int)$store->getId());

        if ($categoriesTree->count() == 0) {
            throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
        }

        $result = $this->extractDataFromCategoryTree->buildTree($categoriesTree, [$rootCategoryId]);
        return current($result);
    }
}
