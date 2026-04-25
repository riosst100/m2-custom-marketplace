<?php

namespace TCGCollective\MarketplaceProductImportExport\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class ImageName extends Column
{
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
                $item[$fieldName] = $item[$fieldName] ?? "-";
            }
        }
        return $dataSource;
    }
}
