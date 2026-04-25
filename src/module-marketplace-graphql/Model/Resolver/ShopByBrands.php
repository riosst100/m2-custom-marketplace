<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Aggregations\Category;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Layered navigation filters resolver, used for GraphQL request processing.
 */
class ShopByBrands implements ResolverInterface
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Model\BrandsFactory
     */
    protected $brandsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $categoryHelper;

    /**
     * @param \TCGCollective\ProductAttributesLink\Model\BrandsFactory $brandsFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        \TCGCollective\ProductAttributesLink\Model\BrandsFactory $brandsFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \Magento\Catalog\Helper\Category $categoryHelper
    ) {
        $this->brandsFactory = $brandsFactory;
        $this->mappingHelper = $mappingHelper;
        $this->categoryHelper = $categoryHelper;
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
        $options = [];
        $filters = [];

        $allowedOptions = [];

        $categoryUrlKey = isset($args['categoryUrlKey']) && $args['categoryUrlKey'] ? $args['categoryUrlKey'] : null;
        if ($categoryUrlKey) {

            // GET FILTERS
            $all = $this->categoryFactory->getStoreCategories(false,true,true);

            // die('<pre>'.print_r($collection->getData(),true));
            // if ($all) {
            //     foreach($all as $category) {
            //         // die('<pre>'.print_r(get_class_methods($category),true));

            //         $filters[] = [
            //             'name' => $category->getName(),
            //             'value' => $category->getUrlKey()
            //         ];
            //     }
            // }

            // GET OPTIONS
            $availableAttributes = [];

            // $collection = $this->brandsFactory->create()
            // ->getCollection()
            // ->addFieldToFilter('entity_type', 'brands');
            // if ($collection->getSize()) {
            //     foreach($collection as $model) {
            //         $label = mb_convert_encoding($model->getName(), "UTF-8");
            //         $attributeCode = $model->getAttributeCode();
                     
            //         $allowedOptions[$attributeCode] = $label;
            //         $availableAttributes[] = $attributeCode;
            //     }
            // }

            if ($availableAttributes) {
                foreach($availableAttributes as $attributeCode) {
                    $optionsRaw = $this->mappingHelper->getAttributeOptions($attributeCode);
                    if ($optionsRaw) {
                        $options = [];
                        foreach ($optionsRaw as $option) {
                            $value = $option->getValue();
                            if ($value) {
                                $label = mb_convert_encoding($option->getLabel(), "UTF-8");
                                if (!$allowedOptions || $allowedOptions && in_array($label, $allowedOptions)) {
                                    $options[] = [
                                        'name' => $label,
                                        'value' => $value,
                                        'attribute_code' => $attributeCode
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'options' => $options,
            'filters' => $filters
        ];
    }
}
