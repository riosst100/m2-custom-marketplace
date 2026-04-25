<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TCGCollective\MarketplaceConfigurableProduct\Ui\DataProvider;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Attributes extends \Magento\ConfigurableProduct\Ui\DataProvider\Attributes
{
    protected $request;
    protected $variationsAttributesMappingFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;
    protected $variationsProductCreationHelper;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler,
        \TCGCollective\MarketPlace\Helper\VariationsProductCreationHelper $variationsProductCreationHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $configurableAttributeHandler, $meta, $data);
        $this->variationsProductCreationHelper = $variationsProductCreationHelper;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $newItems = [];

        $data = $this->variationsProductCreationHelper->getVariationsProductCreationDataProvider('select_attributes');

        $items = $data['items'];

        return [
            'totalRecords' => $data['totalRecords'] ?? 0,
            'items' => $items,
        ];
    }
}
