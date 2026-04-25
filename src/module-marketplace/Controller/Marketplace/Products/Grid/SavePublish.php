<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Products\Grid;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Context;

class SavePublish extends \Magento\Framework\App\Action\Action
{
    protected $jsonFactory;
    protected $productsHelper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        \TCGCollective\MarketPlace\Helper\Products $productsHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->productsHelper = $productsHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        $productId = $this->getRequest()->getParam('product_id');
        $publishDataArr  = $this->getRequest()->getParam('websites');

        if (!$productId || !$publishDataArr) {
            throw new LocalizedException(__('Invalid data.'));
        }

        $publishResult = $this->productsHelper->publishProduct($productId, $publishDataArr);

        return $result->setData($publishResult);
    }
}