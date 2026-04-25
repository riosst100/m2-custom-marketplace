<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;

/**
 * Associated products helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssociatedProducts extends \Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts 
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ConfigurableType
     */
    protected $configurableType;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var array
     */
    protected $productMatrix = [];

    /**
     * @var array
     */
    protected $productAttributes = [];

    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @var VariationMatrix
     */
    protected $variationMatrix;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ConfigurableType $configurableType
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param VariationMatrix $variationMatrix
     * @param CurrencyInterface $localeCurrency
     * @param JsonHelper $jsonHelper
     * @param ImageHelper $imageHelper
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ConfigurableType $configurableType,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        VariationMatrix $variationMatrix,
        CurrencyInterface $localeCurrency,
        JsonHelper $jsonHelper,
        ImageHelper $imageHelper,
        Escaper $escaper = null
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->variationMatrix = $variationMatrix;
        $this->localeCurrency = $localeCurrency;
        $this->jsonHelper = $jsonHelper;
        $this->imageHelper = $imageHelper;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);

        parent::__construct(
            $locator,
            $urlBuilder,
            $configurableType,
            $productRepository,
            $stockRegistry,
            $variationMatrix,
            $localeCurrency,
            $jsonHelper,
            $imageHelper,
            $escaper
        );
    }

    /**
     * Prepare variations
     *
     * @return void
     * @throws CurrencyException
     */
    protected function prepareVariations(): void
    {
        $variations = $this->getVariations();
        $productMatrix = [];
        $attributes = [];
        $productIds = [];
        if ($variations) {
            $usedProductAttributes = $this->getUsedAttributes();
            $productByUsedAttributes = $this->getAssociatedProducts();
            $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());
            $configurableAttributes = $this->getAttributes();
            foreach ($variations as $variation) {
                $attributeValues = [];
                foreach ($usedProductAttributes as $attribute) {
                    $attributeValues[$attribute->getAttributeCode()] = $variation[$attribute->getId()]['value'];
                }
                $key = implode('-', $attributeValues);
                if (isset($productByUsedAttributes[$key])) {
                    $product = $productByUsedAttributes[$key];
                    $price = $product->getPrice();
                    $variationOptions = [];
                    foreach ($usedProductAttributes as $attribute) {
                        if (!isset($attributes[$attribute->getAttributeId()])) {
                            $attributes[$attribute->getAttributeId()] = [
                                'code' => $attribute->getAttributeCode(),
                                'label' => $attribute->getStoreLabel(),
                                'id' => $attribute->getAttributeId(),
                                'position' => $configurableAttributes[$attribute->getAttributeId()]['position'],
                                'chosen' => [],
                            ];
                            $options = $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : [];
                            foreach ($options as $option) {
                                if (!empty($option['value'])) {
                                    $attributes[$attribute->getAttributeId()]['options'][$option['value']] = [
                                        'attribute_code' => $attribute->getAttributeCode(),
                                        'attribute_label' => $attribute->getStoreLabel(0),
                                        'id' => $option['value'],
                                        'label' => $option['label'],
                                        'value' => $option['value'],
                                    ];
                                }
                            }
                        }
                        $optionId = $variation[$attribute->getId()]['value'];
                        $variationOption = [
                            'attribute_code' => $attribute->getAttributeCode(),
                            'attribute_label' => $attribute->getStoreLabel(0),
                            'id' => $optionId,
                            'label' => $variation[$attribute->getId()]['label'],
                            'value' => $optionId,
                        ];
                        $variationOptions[] = $variationOption;
                        $attributes[$attribute->getAttributeId()]['chosen'][$optionId] = $variationOption;
                    }

                    $productMatrix[] = [
                        'id' => $product->getId(),
                        'product_link' => '<a href="' . $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getId()]
                        ) . '" target="_blank">' . $this->escaper->escapeHtml($product->getName()) . '</a>',
                        'sku' => $product->getSku(),
                        'name' => $this->escaper->escapeHtml($product->getName()),
                        'qty' => $this->getProductStockQty($product),
                        'visibility' => $product->getVisibility(),
                        'price' => (float)$price,
                        'price_string' => $currency->toCurrency(sprintf("%f", $price)),
                        'price_currency' => $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol(),
                        'configurable_attribute' => $this->getJsonConfigurableAttributes($variationOptions),
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                        'variationKey' => $this->getVariationKey($variationOptions),
                        'canEdit' => 0,
                        'newProduct' => 0,
                        'attributes' => $this->getTextAttributes($variationOptions),
                        'thumbnail_image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
                    ];
                    $productIds[] = $product->getId();
                }
            }
        }

        $this->productMatrix = $productMatrix;
        $this->productIds = $productIds;
        $this->productAttributes = array_values($attributes);
    }

    /**
     * Prepares text list of used attributes
     *
     * @param array $options
     * @return string
     */
    protected function getTextAttributes(array $options = [])
    {
        $text = '';
        foreach ($options as $option) {
            if ($text) {
                $text .= ', ';
            }
            $text .= $option['label'];
        }

        return $text;
    }

    //phpcs: enable
}
