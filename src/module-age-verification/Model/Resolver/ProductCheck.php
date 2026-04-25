<?php
declare(strict_types=1);

namespace TCGCollective\AgeVerification\Model\Resolver;

use Lof\AgeVerification\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductCheck implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var HelperData
     */
    private $helper;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        HelperData $helper
    ) {
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * resolve
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $sku = $args['sku'] ?? null;
        $storeId = $args['storeId'] ?? null;

        if (!$sku) {
            throw new \InvalidArgumentException(__('SKU is required'));
        }

        try {
            $product = $this->productRepository->get($sku, false, $storeId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Product with SKU "%1" was not found.', $sku));
        }

        $prevent = false;        
        $verifyAge = $this->helper->getVerifyAge($product, $storeId);
        $isRequiredLoginAndValidAge = $this->helper->isRequiredLoginAndValidAge($product);
        $useSpecific = $product->getData('age_verification')['use_custom'];
        $preventView = $product->getData('age_verification')['prevent_view'];        

        try {
            $prevent = (bool)$this->helper->isPreventPurchaseProduct($product);
        } catch (\Exception $e) {
            $prevent = false;
        }

        return [
            'useSpecific' => $useSpecific,
            'preventView' => $preventView,
            'preventPurchase' => $prevent,
            'verifyAge' => $verifyAge,
            'isRequiredLoginAndValidAge' => $isRequiredLoginAndValidAge
        ];
    }
}
