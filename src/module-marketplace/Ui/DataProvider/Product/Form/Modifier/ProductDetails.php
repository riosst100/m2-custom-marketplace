<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Magento\Framework\Stdlib\ArrayManager;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class ProductDetails extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet
{
    const GROUP_CATEGORIES = 'categories';

    /**
     * Set collection factory
     *
     * @var CollectionFactory
     * @since 101.0.0
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var UrlInterface
     * @since 101.0.0
     */
    protected $urlBuilder;

    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    protected $arrayManager;
    protected $mappingHelper;

    /**
     * @param LocatorInterface $locator
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        CollectionFactory $attributeSetCollectionFactory,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        MappingHelper $mappingHelper
    ) {
        $this->locator = $locator;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->mappingHelper = $mappingHelper;

        parent::__construct(
            $locator,
            $attributeSetCollectionFactory,
            $urlBuilder,
        );
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $name = $this->getGeneralPanelName($meta);
        if ($name) {
            $meta[$name]['arguments']['data']['config']['label'] = __('Product Details');
            $meta[$name]['arguments']['data']['config']['collapsible'] = false;
            $meta[$name]['arguments']['data']['config']['opened'] = true;
            $meta[$name]['arguments']['data']['config']['sortOrder'] = 15;
        }

        $meta['category-details']['arguments']['data']['config']['collapsible'] = false;
        $meta['category-details']['arguments']['data']['config']['label'] = 'Categories';
        $meta['category-details']['arguments']['data']['config']['sortOrder'] = 1;

        if (isset($meta['pre-orders'])) {
            $meta['pre-orders']['arguments']['data']['config']['collapsible'] = false;
            $meta['pre-orders']['arguments']['data']['config']['componentType'] = Fieldset::NAME;
            $meta['pre-orders']['arguments']['data']['config']['sortOrder'] = 11;
        }

        $meta['content']['arguments']['data']['config']['sortOrder'] = 17;

        $meta['content']['arguments']['data']['config']['collapsible'] = false;

        return $meta;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        
        $attrSetId = $product->getData('attribute_set_id');
        if ($attrSetId) {
            $attributeSetMapping = $this->mappingHelper->getMappingByAttributeSetId($attrSetId);
            if ($attributeSetMapping) {
                // SET MAPPING PRIORITY
                $path = $productId . '/' . self::DATA_SOURCE_DEFAULT . '/' . 'mapping_priority';
                $data = $this->arrayManager->set($path, $data, $attributeSetMapping->getData('priority'));

                // SET MAPPING GROUP NAME
                $path = $productId . '/' . self::DATA_SOURCE_DEFAULT . '/' . 'mapping_group_name';
                $data = $this->arrayManager->set($path, $data, $attributeSetMapping->getData('group_name'));
            }
        }

        $toysLine = $product->getData('toys_line');
        if ($toysLine) {
            $path = $productId . '/' . self::DATA_SOURCE_DEFAULT . '/' . 'toys_line';
            $data = $this->arrayManager->set($path, $data, $this->mappingHelper->getOptionLabelById('toys_line', $toysLine));
        }
        
        return $data;
    }
}
