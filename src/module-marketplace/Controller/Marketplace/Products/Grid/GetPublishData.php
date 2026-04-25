<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Products\Grid;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class GetPublishData extends Action
{
    protected $resultJsonFactory;
    protected $productsHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \TCGCollective\MarketPlace\Helper\Products $productsHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productsHelper = $productsHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $currentWebsiteIds = $this->getRequest()->getParam('website_ids');

        $publishData = $this->productsHelper->getPublishData($productId, $currentWebsiteIds);

        $result = $this->resultJsonFactory->create();
        return $result->setData($publishData);
    }
}