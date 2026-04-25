<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\OrderItem;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductImage implements ResolverInterface
{
    protected $productRepository;
    protected $storeManager;
    protected $imageHelper;        

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,        
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model']) || !$value['model'] instanceof OrderItemInterface) {
            return null;
        }

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $value['model'];
        $sku = $orderItem->getSku();

        try {
            $productImageUrl = $this->initImage($orderItem)->resize(150)->getUrl();
            // dd();
            return $productImageUrl;

            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
            return null;
        }

        $image = $product->getSmallImage();
        if (!$image || $image === 'no_selection') {
            return null;
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . 'catalog/product' . $image;
    }

    public function initImage($item)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->loadByAttribute('sku', $item->getData('sku'));
        return $this->imageHelper->init($product, 'product_page_image_small', ['type' => 'small_image']);
    }
}
