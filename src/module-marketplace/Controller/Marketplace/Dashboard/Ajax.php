<?php
namespace TCGCollective\MarketPlace\Controller\Marketplace\Dashboard;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Ajax extends Action
{
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $country   = $this->getRequest()->getParam('country');
        $category  = $this->getRequest()->getParam('category');
        $dateRange = $this->getRequest()->getParam('date');
        // dd($dateRange);

        $html = $this->_view->getLayout()
        ->createBlock(\Lof\MarketPlace\Block\Seller\Dashboard::class)
        ->setTemplate('TCGCollective_MarketPlace::seller/dashboard.phtml')
        ->setData('filters', [
            'country'   => $country,
            'category'  => $category,
            'date_range'=> $dateRange
        ])
        ->toHtml();

        // TODO: ambil collection sesuai filter
        // Contoh dummy response
        $data = [
            'success' => true,
            'html' => $html
        ];

        return $result->setData($data);
    }
}
