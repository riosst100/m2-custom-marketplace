<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\OrderItem;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Lof\MarketPlace\Helper\Data as SellerHelper;

class Seller implements ResolverInterface
{
    protected $productRepository;
    protected $sellerHelper;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SellerHelper $sellerHelper
    ) {
        $this->productRepository = $productRepository;
        $this->sellerHelper = $sellerHelper;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/seller.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        if (!isset($value['model']) || !$value['model'] instanceof OrderItemInterface) {
            return null;
        }

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $value['model'];
        $sku = $orderItem->getSku();
        // $logger->info('SKU: '.$sku);

        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return null;
        }
        
        $sellerId = $product->getData('seller_id');
        // $logger->info('Seller Id: '.$sellerId);
        if (!$sellerId) {
            return null;
        }

        // return seller name by ID
        $seller = $this->sellerHelper->getSellerById($sellerId);        
        if (is_object($seller)) {
            // $logger->info('Seller: '.json_encode($seller->getName()));
            return $seller->getName();
        }

        // Or directly return from attribute if you store seller name on product
        return $seller ?: null;
    }
}
