<?php
namespace TCGCollective\MarketplaceProductPreorder\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;

class GetPaymentType implements ResolverInterface
{
    public function resolve(
        $field,
        $context,
        $info,
        array $value = null,
        array $args = null
    ) {
        return [
            [
                'label' => 'Full Payment',
                'value' => 'full'
            ],
            [
                'label' => 'Deposit',
                'value' => 'deposit'
            ]
        ];
    }
}
