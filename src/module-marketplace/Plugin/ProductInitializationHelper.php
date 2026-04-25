<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Catalog\Model\Product\Authorization as ProductAuthorization;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;

class ProductInitializationHelper
{
    /**
     * @var ProductAuthorization
     */
    private $productAuthorization;

    /**
     * @var VariationHandler
     */
    private $variationHandler;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Factory
     */
    private $optionsFactory;

    /**
     * @var array
     */
    private $keysPost = [
        'status',
        'sku',
        'name',
        'price',
        'configurable_attribute',
        'weight',
        'media_gallery',
        'swatch_image',
        'small_image',
        'thumbnail',
        'image'
    ];
    
    public function __construct(
        VariationHandler $variationHandler,
        RequestInterface $request,
        Factory $optionsFactory,
        ProductAuthorization $productAuthorization
    ) {
        $this->variationHandler = $variationHandler;
        $this->request = $request;
        $this->optionsFactory = $optionsFactory;
        $this->productAuthorization = $productAuthorization;
    }

    public function aroundInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject, 
        \Closure $proceed,
        Product $product
    ) {
        // $productData = $this->request->getParam('product');
        if ($this->request->getParam('product') !== null) {
            $productData = $this->request->getParam('product');
        } else {
            $productData = $this->request->getPost('product', []);
            if (empty($productData)) {
                $productData = $this->request->getParam('product', []);
            }
        }
        
        $product = $subject->initializeFromData($product, $productData);
        // $this->productAuthorization->authorizeSavingOf($product);

        return $product;
    }

    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject, 
        ProductInterface $product)
    {        
        if ($this->request->getParam('attributes') !== null) {
            $attributes = $this->request->getParam('attributes');
        } else {
            $attributes = $this->request->getParam('attributes', $this->request->getPost('attributes', []));
        }
        $productData = $this->request->getPost('product', []);
        if (empty($productData)) {
            $productData = $this->request->getParam('product', []);
        }

        if ($product->getTypeId() !== ConfigurableProduct::TYPE_CODE || empty($attributes)) {
            return $product;
        }

        $setId = $this->request->getPost('new-variations-attribute-set-id')
            ?? $this->request->getParam('new-variations-attribute-set-id')
            ?? $this->request->getParam('new_variations_attribute_set_id');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $extensionAttributes = $product->getExtensionAttributes();

        $product->setNewVariationsAttributeSetId($setId);

        $configurableOptions = [];
        if (!empty($productData['configurable_attributes_data'])) {
            $configurableOptions = $this->optionsFactory->create(
                (array) $productData['configurable_attributes_data']
            );
        }

        $extensionAttributes->setConfigurableProductOptions($configurableOptions);

        $this->setLinkedProducts($product, $extensionAttributes);
        $product->setCanSaveConfigurableAttributes(
            (bool) $this->request->getPost('affect_configurable_product_attributes')
        );

        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }

    /**
     * Relate simple products to configurable
     *
     * @param ProductInterface $product
     * @param ProductExtensionInterface $extensionAttributes
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setLinkedProducts(ProductInterface $product, ProductExtensionInterface $extensionAttributes)
    {
        $associatedProductIds = $this->request->getPost('associated_product_ids_serialized', '[]');
        
        if ($associatedProductIds === null) {
            $associatedProductIds = $this->request->getParam('associated_product_ids_serialized', '[]');
        }

        if ($associatedProductIds != null && !empty($associatedProductIds)) {
            $associatedProductIds = json_decode($associatedProductIds, true);
        }

        $variationsMatrix = $this->getVariationMatrix();

        if ($associatedProductIds || $variationsMatrix) {
            $this->variationHandler->prepareAttributeSet($product);
        }

        if (!empty($variationsMatrix)) {
            $generatedProductIds = $this->variationHandler->generateSimpleProducts($product, $variationsMatrix);
            $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
        }
        
        $extensionAttributes->setConfigurableProductLinks(array_filter($associatedProductIds));
    }

    /**
     * Get variation-matrix from request
     *
     * @return array
     */
    protected function getVariationMatrix()
    {
        $result = [];
        $configurableMatrix = $this->request->getParam('configurable-matrix-serialized');
        if ($configurableMatrix === null) {
            $configurableMatrix = $this->request->getParam('configurable_matrix_serialized', $this->request->getPost('configurable-matrix-serialized', "[]"));
        }
        if (!$configurableMatrix) {
            $configurableMatrix = $this->request->getPost('configurable-matrix-serialized', $this->request->getPost('configurable_matrix_serialized', "[]"));
        }
        
        if (isset($configurableMatrix) && $configurableMatrix != "") {
            $configurableMatrix = json_decode($configurableMatrix, true);

            foreach ($configurableMatrix as $item) {
                // dd($item);
                if (isset($item['newProduct']) && $item['newProduct']) {
                    $result[$item['variationKey']] = $this->mapData($item);

                    if (isset($item['qty'])) {
                        $result[$item['variationKey']]['quantity_and_stock_status']['qty'] = $item['qty'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Map data from POST
     *
     * @param array $item
     * @return array
     */
    private function mapData(array $item)
    {
        $result = [];

        foreach ($this->keysPost as $key) {
            if (isset($item[$key])) {
                $result[$key] = $item[$key];
            }
        }

        return $result;
    }
}
