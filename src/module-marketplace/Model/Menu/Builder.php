<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TCGCollective\MarketPlace\Model\Menu;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Menu builder object. Retrieves commands (\Magento\Backend\Model\Menu\Builder\AbstractCommand)
 * to build menu (\Magento\Backend\Model\Menu)
 * @api
 * @since 100.0.2
 */
class Builder extends \Magento\Backend\Model\Menu\Builder
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\AbstractCommand[]
     */
    protected $_commands = [];

    /**
     * @var \Magento\Backend\Model\Menu\Item\Factory
     */
    protected $_itemFactory;

    protected $categoryCollectionFactory;

    /**
     * @param \Magento\Backend\Model\Menu\Item\Factory $menuItemFactory
     */
    public function __construct(
        \Magento\Backend\Model\Menu\Item\Factory $menuItemFactory,
        CollectionFactory $categoryCollectionFactory
    )
    {
        $this->_itemFactory = $menuItemFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct(
            $menuItemFactory
        );
    }

    /**
     * Populate menu object
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return \Magento\Backend\Model\Menu
     * @throws \OutOfRangeException in case given parent id does not exists
     */
    public function getResult(\Magento\Backend\Model\Menu $menu)
    {
        /** @var $items \Magento\Backend\Model\Menu\Item[] */
        $params = [];
        $items = [];

        // Create menu items
        foreach ($this->_commands as $id => $command) {
            $params[$id] = $command->execute();
            $item = $this->_itemFactory->create($params[$id]);
            $items[$id] = $item;
        }

        // $dynamicMenuItems = [
        //     [
        //         "type" => "add",
        //         "id" => "TCGCollective_MarketPlace::catalog_games",
        //         "title" => "Games",
        //         "module" => "TCGCollective_MarketPlace",
        //         "sortOrder" => "1",
        //         "resource" => "TCGCollective_MarketPlace::catalog_games",
        //         "parent" => "TCGCollective_MarketPlace::catalog_products"
        //     ],
        //     [
        //         "type" => "add",
        //         "id" => "TCGCollective_MarketPlace::catalog_cardgame",
        //         "title" => "Collectible Card Game",
        //         "module" => "TCGCollective_MarketPlace",
        //         "sortOrder" => "10",
        //         "action" => "catalog/products/index/category/cardgame",
        //         "resource" => "TCGCollective_MarketPlace::catalog_cardgame",
        //         "parent" => "TCGCollective_MarketPlace::catalog_games"
        //     ]
        // ];
        $dynamicMenuItems = [];

        $parentId = 2; // biasanya root catalog (cek di catalog_category_entity)

        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('parent_id', $parentId)
            ->addIsActiveFilter()
            ->setOrder('position', 'ASC');

        foreach ($categories as $category) {

            // LEVEL 1
            $dynamicMenuItems[] = [
                "type" => "add",
                "id" => "TCGCollective_MarketPlace::catalog_" . $category->getId(),
                "title" => $category->getName(),
                "module" => "TCGCollective_MarketPlace",
                "sortOrder" => $category->getPosition(),
                "resource" => "TCGCollective_MarketPlace::catalog_" . $category->getId(),
                "parent" => "TCGCollective_MarketPlace::catalog_products"
            ];

            $dynamicMenuItems[] = [
                "type" => "add",
                "id" => "TCGCollective_MarketPlace::catalog_variations_" . $category->getId(),
                "title" => $category->getName(),
                "module" => "TCGCollective_MarketPlace",
                "sortOrder" => $category->getPosition(),
                "resource" => "TCGCollective_MarketPlace::catalog_variations_" . $category->getId(),
                "parent" => "TCGCollective_MarketPlace::my_configurable_products"
            ];

            // LEVEL 2 (SUB CATEGORY)
            $subCategories = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('parent_id', $category->getId())
                ->addIsActiveFilter()
                ->setOrder('position', 'ASC');

            foreach ($subCategories as $sub) {
                $dynamicMenuItems[] = [
                    "type" => "add",
                    "id" => "TCGCollective_MarketPlace::catalog_variations_" . $sub->getId(),
                    "title" => $sub->getName(),
                    "module" => "TCGCollective_MarketPlace",
                    "sortOrder" => $sub->getPosition(),
                    "action" => "catalog/products/variations/category/" . $sub->getId(),
                    "resource" => "TCGCollective_MarketPlace::catalog_variations_" . $sub->getId(),
                    "parent" => "TCGCollective_MarketPlace::catalog_variations_" . $category->getId()
                ];

                $dynamicMenuItems[] = [
                    "type" => "add",
                    "id" => "TCGCollective_MarketPlace::catalog_" . $sub->getId(),
                    "title" => $sub->getName(),
                    "module" => "TCGCollective_MarketPlace",
                    "sortOrder" => $sub->getPosition(),
                    "action" => "catalog/products/index/category/" . $sub->getId(),
                    "resource" => "TCGCollective_MarketPlace::catalog_" . $sub->getId(),
                    "parent" => "TCGCollective_MarketPlace::catalog_" . $category->getId()
                ];
            }
        }

        if ($dynamicMenuItems) {
            foreach($dynamicMenuItems as $data) {
                $id = $data['id'];

                $params[$id] = $data;

                $item = $this->_itemFactory->create($data);
                $items[$id] = $item;
            }
        }

        // Build menu tree based on "parent" param
        foreach ($items as $id => $item) {
            $sortOrder = $this->_getParam($params[$id], 'sortOrder');
            $parentId = $this->_getParam($params[$id], 'parent');
            $isRemoved = isset($params[$id]['removed']);

            if ($isRemoved) {
                continue;
            }
            if (!$parentId) {
                $menu->add($item, null, $sortOrder);
            } else {
                if (!isset($items[$parentId])) {
                    throw new \OutOfRangeException(sprintf('Specified invalid parent id (%s)', $parentId));
                }
                if (isset($params[$parentId]['removed'])) {
                    continue;
                }
                $items[$parentId]->getChildren()->add($item, null, $sortOrder);
            }
        }

        return $menu;
    }
}
