<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace TCGCollective\MarketPlace\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Class RegistryLocator
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Registry $registry, StoreManagerInterface $storeManager)
    {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getProduct()
    {
        if (null !== $this->product) {
            return $this->product;
        }

        if ($product = $this->registry->registry('current_product')) {
            return $this->product = $product;
        }

        throw new NotFoundException(__("The product wasn't registered."));
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getStore()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if ($store = $this->storeManager->getStore()) {
            return $this->store = $store;
        }

        throw new NotFoundException(__("The store wasn't registered. Verify the store and try again."));
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getProduct()->getWebsiteIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getStore()->getBaseCurrencyCode();
    }
}
