<?php

namespace TCGCollective\PlatformDatabase\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Categories implements ArrayInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = [];

        $options[] = ['label' => '', 'value' => ''];

        // Get category collection (exclude root category)
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('level', ['eq' => 3])
            ->addIsActiveFilter()
            ->setOrder('path', 'ASC');

        foreach ($collection as $category) {
            $options[] = [
                'label' => $category->getParentCategory()->getName() . ' | ' . $category->getName(),
                'value' => $category->getId()
            ];
        }

        usort($options, fn($a, $b) => $a['label'] <=> $b['label']);

        return $options;
    }
}