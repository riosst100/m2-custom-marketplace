<?php

namespace TCGCollective\SellerMembership\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Duration extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as $key => $item) {

            $duration = $item['duration'] ?? null;
            $unit = $item['duration_unit'] ?? null;

            if ($duration === null || $unit === null) {
                $value = __('N/A');
            } else {
                $value = $duration . ' ' . $unit;
            }

            $dataSource['data']['items'][$key][$fieldName] = $value;
        }

        return $dataSource;
    }
}
