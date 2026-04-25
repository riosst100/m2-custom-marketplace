<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Messages;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lof\MarketplaceGraphQl\Model\Resolver\AbstractSellerQuery;

class Seller extends AbstractSellerQuery implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {   
        $data = [];

        $sellerId = $value['seller_send'];
        if (!$sellerId) {
            return $data;
        }

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->sellerCache = $objectManager->create('\TCGCollective\MarketPlace\Model\SellerDataCache');

            $sellerData = $this->sellerCache->getSellerById($sellerId);
            $data = $sellerData ?: [];
            $data["model"] = $sellerData;
        } catch (\Exception $e) {
            //
        }

        return $data;
    }
}
