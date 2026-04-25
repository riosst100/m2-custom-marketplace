<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class CardName implements ResolverInterface
{
    /**
     * @var MappingHelper
     */
    protected $_mappingHelper;

    /**
     * @param MappingHelper $mappingHelper
     */
    public function __construct(
        MappingHelper $mappingHelper
    ) {
        $this->_mappingHelper = $mappingHelper;
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

        /** @var Product $product */
        $product = $value['model'];

        return $this->_mappingHelper->getOptionLabelById('card_name', $product->getData('card_name'));
    }
}
