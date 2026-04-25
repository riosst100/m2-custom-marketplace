<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TCGCollective\MarketPlace\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Block\MenuItemChecker;
use Magento\Framework\Escaper;

/**
 * Class AnchorRenderer
 */
class AnchorRenderer extends \Magento\Backend\Block\AnchorRenderer
{
    /**
     * @var MenuItemChecker
     */
    private $menuItemChecker;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param MenuItemChecker $menuItemChecker
     * @param Escaper $escaper
     */
    public function __construct(
        MenuItemChecker $menuItemChecker,
        Escaper $escaper
    ) {
        $this->menuItemChecker = $menuItemChecker;
        $this->escaper = $escaper;

        parent::__construct(
            $menuItemChecker,
            $escaper
        );
    }

    /**
     * Render menu item anchor.
     *
     *  It is used in backend menu to render anchor menu.
     *
     * @param Item|false $activeItem Can be false if menu item is inaccessible
     * but was triggered directly using controller. It is a legacy code behaviour.
     * @param Item $menuItem
     * @param int $level
     * @return string
     */
    public function renderAnchor($activeItem, Item $menuItem, $level)
    {
        /* ===== CUSTOM START ===== */

        $extra = '';

        if ($menuItem->getId() == "TCGCollective_MarketPlace::add_product") {
            $extra = '<div style="
                font-size:13px;
                color: #7a8b9d;
                margin-top: 5px;
                line-height:1.4;
            ">
            Create a new product by entering all product details from scratch.
            </div>';
        }
        if ($menuItem->getId() == "TCGCollective_MarketPlace::add_product_database") {
            $extra = '<div style="
                font-size: 13px;
                color: #7a8b9d;
                margin-top: 5px;
                line-height: 1.4;
            ">
            Create a product instantly using data from the platform database.
            </div>';
        }
        if ($menuItem->getId() == "TCGCollective_MarketPlace::add_configurable_product") {
            $extra = '<div style="
                
                font-size:13px;
                color:#7a8b9d;
                margin-top: 5px;
                line-height:1.4;
            ">
            Create a product with multiple variations such as condition, edition, or version.
            </div>';
        }

        /* ===== CUSTOM END ===== */

        if ($level == 1 && $menuItem->getUrl() == '#') {
            $output = '';
            if ($menuItem->hasChildren()) {
                $output = '<strong class="submenu-group-title" role="presentation">'
                    . '<span>' . $this->escaper->escapeHtml(__($menuItem->getTitle())) . '</span>'
                    . '</strong>';
            }
        } else {
            $target = $menuItem->getTarget() ? ('target=' . $menuItem->getTarget()) : '';
            $output = '<a href="' . $menuItem->getUrl() . '" ' . $target . ' ' . $this->_renderItemAnchorTitle(
                $menuItem
            ) . $this->_renderItemOnclickFunction(
                $menuItem
            ) . ' class="' . ($this->menuItemChecker->isItemActive($activeItem, $menuItem, $level) ? '_active' : '')
                . '">' . '<span>' . $this->escaper->escapeHtml(__($menuItem->getTitle()))
                . '</span>' . $extra . '</a>';
        }

        return $output;
    }

    /**
     * Render menu item anchor title
     *
     * @param Item $menuItem
     * @return string
     */
    private function _renderItemAnchorTitle($menuItem)
    {
        return $menuItem->hasTooltip() ? 'title="' . __($menuItem->getTooltip()) . '"' : '';
    }

    /**
     * Render menu item onclick function
     *
     * @param Item $menuItem
     * @return string
     */
    private function _renderItemOnclickFunction($menuItem)
    {
        return $menuItem->hasClickCallback() ? ' onclick="' . $menuItem->getClickCallback() . '"' : '';
    }
}
