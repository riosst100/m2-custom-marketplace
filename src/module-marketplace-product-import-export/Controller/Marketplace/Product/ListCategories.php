<?php
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use TCGCollective\MarketplaceProductImportExport\Model\Source\MainCategories;

class ListCategories extends Action
{
    protected $resultJsonFactory;
    protected $sellerFactory;
    protected $customerSession;
    protected $mainCategories;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SellerFactory $sellerFactory,
        CustomerSession $customerSession,
        MainCategories $mainCategories
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sellerFactory     = $sellerFactory;
        $this->customerSession   = $customerSession;
        $this->mainCategories    = $mainCategories;
    }

    /**
     * Get seller ID from customer session
     *
     * @return int
     */
    protected function getSellerId()
    {
        $customerId = $this->customerSession->getId();
        if (!$customerId) {
            return 0;
        }

        $collection = $this->sellerFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setPageSize(1);

        return (int) $collection->getFirstItem()->getData('seller_id');
    }

    public function execute()
    {
        $result   = $this->resultJsonFactory->create();
        $sellerId = $this->getSellerId();

        if (!$sellerId) {
            return $result->setData(['success' => false, 'message' => 'No seller session']);
        }

        // Get all categories (optionally you can filter by seller logic here if needed)
        $categoriesData = $this->mainCategories->toOptionArray();
        $categories = [];

        if ($categoriesData) {
            foreach ($categoriesData as $data) {
                $categories[] = [
                    'name' => $data['label'],
                    'id'   => $data['value']
                ];
            }
        }

        return $result->setData([
            'success'    => true,
            'categories' => $categories
        ]);
    }
}
