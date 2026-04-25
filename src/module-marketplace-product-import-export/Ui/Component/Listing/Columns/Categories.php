<?php

namespace TCGCollective\MarketplaceProductImportExport\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Categories extends Column
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mainCategories = $objectManager->create(\TCGCollective\MarketplaceProductImportExport\Model\Source\MainCategories::class);
        
        $categoriesData = $this->mainCategories->toOptionArray();

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                // $ext = $item['content_type'] ? explode("/", $item['content_type'])[1] : '';
                // $item[$fieldName] = $item[$fieldName] ? $item[$fieldName] . '.' . $ext : "-";
                $categories = null;

                $searchValue = $item[$fieldName] ?? null;
                if ($searchValue) {
                    $filteredCategory = array_filter($categoriesData, function ($category) use ($searchValue) {
                        return $category['value'] === $searchValue;
                    });

                    $result = reset($filteredCategory);
                    if ($result) {
                        $categories = $result['label'] ?? '';
                    }
                }

                $item[$fieldName] = $categories ?? "-";
            }
        }
        return $dataSource;
    }
}
