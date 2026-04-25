<?php
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;

class CreateFolder extends Action
{
    protected $resultJsonFactory;
    protected $sellerFactory;
    protected $customerSession;
    protected $directoryList;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SellerFactory $sellerFactory,
        CustomerSession $customerSession,
        DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sellerFactory     = $sellerFactory;
        $this->customerSession   = $customerSession;
        $this->directoryList     = $directoryList;
    }

    protected function getSellerId(): int
    {
        $customerId = (int) $this->customerSession->getId();
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
        $result  = $this->resultJsonFactory->create();
        $sellerId = $this->getSellerId();
        if (!$sellerId) {
            return $result->setData(['success' => false, 'message' => 'Not seller session']);
        }

        $name   = trim((string) $this->getRequest()->getParam('name'));
        $parent = trim((string) $this->getRequest()->getParam('parent', ''));

        if ($name === '') {
            return $result->setData(['success' => false, 'message' => 'Folder name required']);
        }

        // Always start inside seller/<seller_id>
        $baseFolder = 'seller/' . $sellerId;

        // If parent is empty, stay at base. If parent is like "test2", prepend seller/<id>.
        if ($parent === '' || $parent === $baseFolder) {
            $relativeParent = $baseFolder;
        } elseif (strpos($parent, $baseFolder) === 0) {
            $relativeParent = rtrim($parent, '/');
        } else {
            $relativeParent = $baseFolder . '/' . ltrim($parent, '/');
        }

        $newPath   = $relativeParent . '/' . $name;
        $mediaPath = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . $newPath;

        if (!is_dir($mediaPath)) {
            @mkdir($mediaPath, 0775, true);
        }

        return $result->setData(['success' => true, 'folder' => $newPath]);
    }
}
