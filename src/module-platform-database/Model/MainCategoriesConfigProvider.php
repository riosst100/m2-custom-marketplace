<?php

namespace TCGCollective\PlatformDatabase\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MainCategoriesConfigProvider 
{
    private static array $cache = [];

    public function __construct(
        \TCGCollective\PlatformDatabase\Model\ResourceModel\MainCategoriesConfig\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        $result = [];

        foreach ($collection as $item) {
            $result[$item->getData('entity_code')] = $item->toConfigArray();
        }

        return $result;
    }

    public function getByEntityCode(string $code): array
    {
        $item = $this->collectionFactory->create()
            ->addFieldToFilter('entity_code', $code)
            ->getFirstItem();

        return $item->getId() ? $item->toConfigArray() : [];
    }

    public function getByCategoryId(int $categoryId): array
    {
        $item = $this->collectionFactory->create()
            ->addFieldToFilter('category_id', $categoryId)
            ->getFirstItem();

        return $item->getId() ? $item->toConfigArray() : [];
    }

    public function getByMainAttrCode(string $attrCode): array
    {
        $collection = $this->collectionFactory->create();

        foreach ($collection as $item) {
            if ($item->getData('main_attr_code') === $attrCode) {
                return $item->toConfigArray();
            }
        }

        return [];
    }

    /**
     * Get collection by custom table name
     */
    public function getCustomTableCollection(string $tableName)
    {
        // if (isset(self::$cache[$tableName]['collection'])) {
        //     return self::$cache[$tableName]['collection'];
        // }

        $om = ObjectManager::getInstance();

        // dd($this->getAllResourceModels());

        foreach ($this->getAllResourceModels() as $collectionClass) {
            
            if (!$collectionClass) {
                continue;
            }

            // dd($collectionClass);

            // if (!is_subclass_of($collectionClass, AbstractDb::class)) {
            //     continue;
            // }

            /** @var AbstractDb $resource */
            $resource = $om->get($collectionClass);

            if (!$resource->getMainTable()) {
                continue;
            }

            if ($resource->getMainTable() !== $tableName) {
                continue;
            }

            // dd($collectionClass);

            // dd('oksdd');

            // $collectionClass = str_replace(
            //     'ResourceModel',
            //     'ResourceModel\\Collection',
            //     $collectionClass
            // );

            // dd('ok'.class_exists($collectionClass));

            // if (!($collectionClass)) {
            //     return null;
            // }

            return self::$cache[$tableName]['collection'] = $om->get($collectionClass . 'Factory')->create();
        }

        return null;
    }

    /**
     * Auto-discover ResourceModels
     * SUPPORT app/code + vendor/riosst100
     */
    private function getAllResourceModels(): array
    {
        static $classes = null;

        if ($classes !== null) {
            return $classes;
        }

        $classes = [];

        $paths = [
            // vendor riosst100 modules
            BP . '/vendor/riosst100/m2-custom-marketplace/src/module-product-attributes-link/Model/ResourceModel/**/*.php',
        ];

        foreach ($paths as $pattern) {
            foreach (glob($pattern) as $file) {

                $content = file_get_contents($file);

                if (
                    preg_match('/namespace\s+([^;]+);/', $content, $ns) &&
                    preg_match('/class\s+(\w+)/', $content, $cls)
                ) {
                    $classes[] = $ns[1] . '\\' . $cls[1];
                }
            }
        }

        return $classes;
    }
}