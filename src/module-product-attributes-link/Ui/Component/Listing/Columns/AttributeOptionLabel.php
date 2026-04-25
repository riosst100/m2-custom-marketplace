<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class AttributeOptionLabel extends Column
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        array $components = [],
        array $data = []
    ) {
        $this->mappingHelper = $mappingHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $label = $this->mappingHelper->getOptionLabelById($fieldName, $item[$fieldName]);
                    if ($label) {
                        $item[$fieldName] = $label;
                    } else {
                        $item[$fieldName] = "-";
                    }
                }
            }
        }
        return $dataSource;
    }
}
