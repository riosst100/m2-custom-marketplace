<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class PreOrderData implements ResolverInterface
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

        $isPreorder = $product->getData('pre_order_payment_type');

        // dd($isPreorder);

        return [
            'is_preorder' => $isPreorder ? true : false,
            'pre_order_payment_type' => $product->getData('pre_order_payment_type'),
            'pre_order_deposit' => $product->getData('pre_order_deposit'),
            'pre_order_notes' => $product->getData('pre_order_notes'),
            'pre_order_date' => $product->getData('pre_order_date')
        ];
    }
}
