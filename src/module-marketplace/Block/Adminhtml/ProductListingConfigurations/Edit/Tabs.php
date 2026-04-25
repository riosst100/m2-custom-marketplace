<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations\Edit;

use Exception;
use TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations\Edit\Tab\CategoryConfiguration;
use Magento\Framework\Exception\LocalizedException;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Content;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Creator;
use Lof\Formbuilder\Block\Adminhtml\Form\Edit\Tab\Meta;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_listing_configuration_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Product Listing Configuration'));

        $this->addTab(
            'main_section',
            [
                'label' => __('Category Configuration'),
                'content' => $this->getLayout()->createBlock(CategoryConfiguration::class)->toHtml()
            ]
        );

        // $this->addTab(
        //     'tabs_section',
        //     [
        //         'label' => __('Tabs Configuration'),
        //         'content' => $this->getLayout()->createBlock(CategoryConfiguration::class)->toHtml()
        //     ]
        // );
    }
}
