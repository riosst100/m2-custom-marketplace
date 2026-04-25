<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Products\Grid;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\StoreManagerInterface;

class ConvertCurrency extends Action
{
    protected $resultJsonFactory;
    protected $currencyFactory;
    protected $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CurrencyFactory $currencyFactory,
        StoreManagerInterface $storeManager
    ){
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $price = (float)$this->getRequest()->getParam('price');
        $baseCurrency = $this->getRequest()->getParam('base_currency');
        $currencies = $this->getRequest()->getParam('currencies');

        $result = [];

        if(!$currencies){
            return $this->resultJsonFactory->create()->setData([
                'success' => false
            ]);
        }

        foreach ($currencies as $currency) {

            if ($currency == $baseCurrency) {
                continue;
            }

            $rate = $this->currencyFactory
                ->create()
                ->load($baseCurrency)
                ->getAnyRate($currency);

            if ($rate) {
                $result[$currency] = round($price * $rate, 2);
            }

        }

        return $this->resultJsonFactory->create()->setData([
            'success' => true,
            'prices' => $result
        ]);
    }
}