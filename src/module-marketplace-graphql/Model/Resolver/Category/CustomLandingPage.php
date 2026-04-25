<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class CustomLandingPage implements ResolverInterface
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    private $mappingHelper;

    /**
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->mappingHelper = $mappingHelper;
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

        $result = [];

        /* @var Category $category */
        $category = $value['model'];
        if ($category) {
            // die(json_encode($category->getData()));
            return $category->getCustomLandingPage();
        }

        return $result;
    }
}
