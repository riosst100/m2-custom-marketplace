<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class AuctionData implements ResolverInterface
{
    /**
     * @var \Lof\Auction\Model\ProductRepository
     */
    protected $auctionProductRepository;

    /**
     * @param \Lof\Auction\Model\ProductRepository $auctionProductRepository
     */
    public function __construct(
        \Lof\Auction\Model\ProductRepository $auctionProductRepository
    ) {
        $this->auctionProductRepository = $auctionProductRepository;
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

        $isAuction = $product->getData('auction');
        if ($isAuction) {
            $auctionProduct = $this->auctionProductRepository->getByProductSku($product->getSku());
            if ($auctionProduct) {
                return array_merge(
                    $auctionProduct->getData(),
                    ['is_auction' => $isAuction ? true : false]
                );
            }
        }

        return [
            'is_auction' => $isAuction ? true : false
        ];
    }
}
