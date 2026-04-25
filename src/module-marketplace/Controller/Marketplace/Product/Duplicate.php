<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product\Action as ProductAction;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Duplicate extends \Magento\Framework\App\Action\Action
{
    protected $websiteDataCache;
    protected $storeDataCache;
    protected $productAction;


    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * Save constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Product\Copier $productCopier
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Lof\MarketPlace\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->productCopier = $productCopier;
        $this->helper = $helper;
    }
    
    public function execute()
    {
        $this->websiteDataCache = $this->_objectManager->create(\TCGCollective\MarketPlace\Model\WebsiteCollectionCache::class);
        $this->storeDataCache = $this->_objectManager->create(\TCGCollective\MarketPlace\Model\StoreCollectionCache::class);
        $this->productAction = $this->_objectManager->create(\Magento\Catalog\Model\Product\Action::class);

        // $storeData = $this->storeDataCache->getData();
        //     dd($storeData);

        try {
            $productId = $this->getRequest()->getParam('id');
            if ($productId) {
                $oldProduct = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
                $newProduct = $this->productCopier->copy($oldProduct->load($productId));
            }

            $model = $this->_objectManager->create(\Lof\MarketPlace\Model\SellerProduct::class);
            $model->setProductId($newProduct->getEntityId())
                // ->setStoreId($this->helper->getCurrentStoreId())
                // ->setSellerId($product->getSellerId())
                ->save();

            $newProduct->setStatus(1)
            ->setPublishStatus(2)
            ->setPrice(0)
            ->setWebsiteIds([])
            ->save();

            

            $websiteCollection = $this->websiteDataCache->getWebsiteCollection();
            if ($websiteCollection) {
                $websites = $websiteCollection['items'] ?? [];
                if ($websites) {
                    foreach ($websites as $website) {
                        $websiteId = $website['website_id'] ?? null;
                        $websiteName = $website['name'] ?? null;
                        $storeData = $this->storeDataCache->getData();
                        if ($storeData) {
                            $stores = $storeData['items'] ?? null;
                            if ($stores) {
                                foreach($stores as $store) {
                                    if ($store['website_id'] == $websiteId) {
                                        try {
                                            $this->productAction->updateAttributes([$newProduct->getId()], [
                                                'publish_status'=> 2
                                            ], $store['store_id']);
                                        } catch (\Exception $e) {
                                            $this->messageManager->addErrorMessage($e->getMessage());
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            // if ($websiteIds) {
            //     foreach($websiteIds as $index => $websiteId) {
                    // $website = $this->storeManager->getWebsite($websiteId);
                    // if ($website) {
                        // $stores = $website->getStores();
                        // if ($stores) {
                        //     foreach($stores as $store) {
                        //         try {
                        //             $this->productAction->updateAttributes([$newProduct->getId()], [
                        //                 'publish_status'=> 1
                        //             ], $store->getId());
                        //         } catch (\Exception $e) {
                        //             $this->messageManager->addErrorMessage($e->getMessage());
                        //         }
                        //     }
                        // }
                    // }
                // }
            // }

            // dd($newProduct->getData());

            // $this->_eventManager->dispatch(
            //     'marketplace_seller_duplicate_product',
            //     ['account_controller' => $this, 'seller_id' => $seller_id, "seller_product" => $model, 'request' => $this->getRequest(), 'store_id' => $storeId]
            // );

            $this->messageManager->addSuccessMessage(__('You duplicated the product.'));

            return $this->_redirect('catalog/product/edit/id/'.$newProduct->getId());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while duplicate product %1.', $e->getMessage()));
        }

        return $this->_redirect('catalog/product/index');
    }
}
