<?php

namespace TCGCollective\ProductAttributesLink\Model\Source;

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

        $importType = $this->getImportType();

        // Get category collection (exclude root category)
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('level', ['eq' => 3])
            ->addIsActiveFilter()
            ->setOrder('path', 'ASC');

        $collection->getSelect()->join(
            ['ct' => $collection->getTable('marketplace_maincategories_config')],
            'ct.category_id = e.entity_id',
            []
        );
        
        if ($importType == "sets") {
            $collection->getSelect()->where('ct.sets_table_name IS NOT NULL');
        }

        if ($importType == "sealedproducts") {
            $collection->getSelect()->where('ct.sealedproducts_table_name IS NOT NULL');
        }

        if ($importType == "singles") {
            $collection->getSelect()->where('ct.singles_table_name IS NOT NULL');
        }

        if ($importType == "attributes") {
            $collection->getSelect()->where('ct.customattributes_table_name IS NOT NULL');
        }

        foreach ($collection as $category) {
            $options[] = [
                'label' => $category->getParentCategory()->getName() . ' | ' . $category->getName(),
                'value' => $category->getId()
            ];
        }

        usort($options, fn($a, $b) => $a['label'] <=> $b['label']);

        return $options;
    }

    public function getImportType() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->urlBuilder = $objectManager->create(\Magento\Framework\UrlInterface::class);

        $currentUrl = $this->urlBuilder->getCurrentUrl();
        if ($currentUrl) {
            if (str_contains($currentUrl, 'import/sets')) {
                return 'sets';
            }

            if (str_contains($currentUrl, 'import/sealedproducts')) {
                return 'sealedproducts';
            }

            if (str_contains($currentUrl, 'import/singles')) {
                return 'singles';
            }

            if (str_contains($currentUrl, 'import/attributes')) {
                return 'attributes';
            }
        }

        return 'sets';
    }
}