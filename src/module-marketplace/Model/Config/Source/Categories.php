<?php

namespace TCGCollective\MarketPlace\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Categories implements OptionSourceInterface
{
    protected $categoryCollectionFactory;

    public function __construct(CollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = [];

        // Ambil kategori aktif level 2 & 3
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['name', 'parent_id'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', ['in' => [2, 3]])
            ->setOrder('path', 'ASC');

        // Bikin map Level 2
        $level2Map = [];
        foreach ($collection as $category) {
            if ((int)$category->getLevel() === 2) {
                $level2Map[$category->getId()] = $category->getName();
            }
        }

        // Tambahkan Level 3, tampil dengan format "Parent ~ Child"
        foreach ($collection as $category) {
            if ((int)$category->getLevel() === 3) {
                $parentName = $level2Map[$category->getParentId()] ?? '';
                $label = $parentName ? sprintf('%s | %s', $parentName, $category->getName()) : $category->getName();

                $options[] = [
                    'value' => $category->getId(),
                    'label' => $label
                ];
            }
        }

        // Urutkan berdasarkan label (A-Z)
        usort($options, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        // Tambahkan opsi kosong di atas
        array_unshift($options, ['value' => '', 'label' => __('-- Please Select --')]);

        return $options;
    }
}
