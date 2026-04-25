<?php

namespace TCGCollective\SellerMembership\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;

class MultipleWebsite extends Column
{
    protected $storesHelper;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \TCGCollective\MarketPlace\Helper\Stores $storesHelper,
        array $components = [],
        array $data = []
    ) {
        $this->storesHelper = $storesHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            $options = $this->storesHelper->getStoresGridOptionsArray();

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName]) && count($item[$fieldName]) > 0) {
                    $websiteIds = $item[$fieldName];
                    if ($websiteIds) {
                        $labels = [];
                        foreach ($websiteIds as $value) {
                            if (isset($options[$value])) {
                                $labels[] = $options[$value];
                            }
                        }

                        $item[$fieldName] = implode(', ', $labels);
                    } else {
                        $item[$fieldName] = '';
                    }
                } else {
                    $item[$fieldName] = '';
                }
            }
        }

        return $dataSource;
    }
}
