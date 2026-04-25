<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class CustomTableMetadata implements ResolverInterface
{
    /**
     * @var \TCGCollective\MarketPlace\Helper\MainCategoriesHelper
     */
    protected $mainCategoriesHelper;
    protected $mappingHelper;

    /**
     * @param \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper
     */
    public function __construct(
        \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->mainCategoriesHelper = $mainCategoriesHelper;
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

        /** @var Product $product */
        $product = $value['model'];

        $mainCategoriesData = $this->mainCategoriesHelper->getMainCategoriesData();
        if ($mainCategoriesData) {
            foreach($mainCategoriesData as $data) {
                $mainAttrCode = $data['mainAttrCode'] ?? null;
                $val = $product->getData($mainAttrCode);
                if ($val) {
                    $label = $this->mappingHelper->getOptionLabelById($mainAttrCode, $val);
                    return array_merge($data, ['mainAttrVal' => $val, 'mainAttrLabel' => $label]);
                }
            }
        }

        return null;
    }
}
