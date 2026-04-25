<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class SaleData implements ResolverInterface
{    
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

        $isSale = $product->getData('sale');

        // $_savingPercent = 100 - round(($_finalPrice / $_price)*100);

        return [
            'is_sale' => $isSale ? true : false,
            'discount' => [
                'amount' => 12,
                'percentage' => 10
            ]
            // 'preorder_notes' => $product->getData('preorder_msg'),
            // 'preorder_availability' => $product->getData('preorder_availability')
        ];
    }
}
