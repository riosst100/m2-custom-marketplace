<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceProductPreview\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;
use Magento\UrlRewriteGraphQl\Model\DataProvider\EntityDataProviderComposite;
use Magento\UrlRewriteGraphQl\Model\Resolver\AbstractEntityUrl;

class Route extends AbstractEntityUrl implements ResolverInterface
{
    /**
     * @var EntityDataProviderComposite
     */
    private $entityDataProviderComposite;

    /**
     * @var \TCGCollective\MarketplaceProductPreview\Helper\Data
     */
    protected $productPreviewHelper;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param CustomUrlLocatorInterface $customUrlLocator
     * @param EntityDataProviderComposite $entityDataProviderComposite
     * @param Uid $idEncoder
     * @param \TCGCollective\MarketplaceProductPreview\Helper\Data $productPreviewHelper
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        CustomUrlLocatorInterface $customUrlLocator,
        EntityDataProviderComposite $entityDataProviderComposite,
        Uid $idEncoder,
        \TCGCollective\MarketplaceProductPreview\Helper\Data $productPreviewHelper
    ) {
        parent::__construct($urlFinder, $customUrlLocator, $idEncoder);
        $this->entityDataProviderComposite = $entityDataProviderComposite;
        $this->productPreviewHelper = $productPreviewHelper;
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
        $result = null;
        $resultArray = parent::resolve(
            $field,
            $context,
            $info,
            $value,
            $args
        );

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $productPreviewToken = $args['product_preview_token'] ?? null;
        if ($productPreviewToken) {
            $urlKey = $args['url'] ? str_replace("/", "", $args['url']) : null;
            if ($urlKey) {
                $productId = $this->productPreviewHelper->getProductIdByUrlKey($urlKey);
                if ($productId) {
                    $isValid = $this->productPreviewHelper->isValidProductPreviewToken($productPreviewToken, $productId);
                    if ($isValid) {
                        $resultArray = [];
                        $resultArray['type'] = 'PRODUCT';
                        $resultArray['id'] = $productId;
                        $resultArray['redirect_code'] = 0;
                        $resultArray['relative_url'] = $urlKey;
                    }
                }
            }
        }

        if ($resultArray) {
            $result = $this->entityDataProviderComposite->getData(
                $resultArray['type'],
                (int)$resultArray['id'],
                $info,
                $storeId
            );
            $result['redirect_code'] = $resultArray['redirect_code'];
            $result['relative_url'] = $resultArray['relative_url'];
            $result['type'] = $resultArray['type'];
            return $result;
        }
        return null;
    }
}
