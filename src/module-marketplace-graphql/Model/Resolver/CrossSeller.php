<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class CrossSeller implements ResolverInterface
{
    protected $catalogHelper;

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
        $result = [];

        return $result;
    }
}
