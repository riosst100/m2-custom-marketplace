<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class Prefixes implements ResolverInterface
{
    /**
     * @var \TCGCollective\MarketPlace\Helper\Catalog
     */
    private $catalogHelper;

    /**
     * @param \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper
     */
    public function __construct(
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var Category $category */
        $category = $value['model'];
        if ($category) {
            $prefixes = $this->catalogHelper->getPrefixesByCategoryId($category->getId());
            return $prefixes;
        }

        return null;
    }
}
