<?php

namespace TCGCollective\SellerMembership\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;

class Website extends Column
{
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName]) && $item[$fieldName] && $item[$fieldName][0] != 0) {
                    try {
                        $website = $this->storeManager->getWebsite($item[$fieldName][0]);
                        $item[$fieldName] = $website->getName();
                    } catch (\Exception $e) {
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
