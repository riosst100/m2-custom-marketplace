<?php
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;

class ListFolders extends Action
{
    protected $resultJsonFactory;
    protected $sellerFactory;
    protected $customerSession;
    protected $dirList;
    protected $fileDriver;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SellerFactory $sellerFactory,
        CustomerSession $customerSession,
        DirectoryList $dirList,
        FileDriver $fileDriver
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sellerFactory     = $sellerFactory;
        $this->customerSession   = $customerSession;
        $this->dirList           = $dirList;
        $this->fileDriver        = $fileDriver;
    }

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
            return $result->setData(['success' => false, 'message' => 'Not seller session']);
        }

        $sellerRoot = $this->dirList->getPath('media') . '/seller/' . $sellerId;

        if (!$this->fileDriver->isExists($sellerRoot)) {
            return $result->setData(['success' => true, 'folders' => []]);
        }

        $folders = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sellerRoot, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // dd($iterator);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                // Path relative to the seller root only
                $relative = ltrim(
                    str_replace($sellerRoot, '', $item->getPathname()),
                    DIRECTORY_SEPARATOR
                );
                if ($relative !== '') {
                    $folders[] = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
                }
            }
        }

        return $result->setData([
            'success' => true,
            'folders' => array_values(array_unique($folders))
        ]);
    }
}
