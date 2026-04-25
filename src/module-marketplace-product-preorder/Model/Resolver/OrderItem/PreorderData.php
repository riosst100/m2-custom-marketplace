<?php

namespace TCGCollective\MarketplaceProductPreorder\Model\Resolver\OrderItem;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class PreorderData implements ResolverInterface
{
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        // Ensure GraphQL has the Quote Item model
        if (!isset($value['model'])) {
            return null;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $value['model'];

        // Determine which field is being resolved
        switch ($info->fieldName) {
            case 'is_preorder':
                return (bool) $item->getData('is_preorder');

            case 'pre_order_payment_type':
                return $item->getData('pre_order_payment_type');

            case 'pre_order_deposit':
                return $item->getData('pre_order_deposit');

            default:
                return null;
        }
    }
}
