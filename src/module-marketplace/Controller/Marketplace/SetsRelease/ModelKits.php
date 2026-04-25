<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\SetsRelease;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;

/**
 * Class SportCards
 */
class ModelKits extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $registry;
    protected $productFactory;
    protected $storeFactory;


    /**
     * @var Builder
     */
    //protected $productBuilder;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        \Magento\Framework\App\RequestInterface $request,
        Registry $registry,
        StoreFactory $storeFactory = null)
    {
        parent::__construct($context);
         $this->request = $request;
         $this->registry = $registry;
         $this->productFactory = $productFactory;
         $this->storeFactory = $storeFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreFactory::class);
       // $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultLayout->getLayout()->getUpdate()->removeHandle('default');

        return $resultLayout;
    }
}
