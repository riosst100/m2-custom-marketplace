<?php
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;

class DeleteFolder extends Action
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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/imageupload.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('Delete folder');

        $result   = $this->resultJsonFactory->create();
        $sellerId = $this->getSellerId();
        if (!$sellerId) {
            return $result->setData(['success' => false, 'message' => 'Not seller session']);
        }

        $folder = trim((string) $this->getRequest()->getParam('folder', ''));
        if ($folder === '') {
            return $result->setData(['success' => false, 'message' => 'Folder required']);
        }

        // dd($folder);
        if ($folder == "Root Folder") {
            return $result->setData(['success' => false, 'message' => 'Cannot delete Root Folder']);
        }

        $baseFolder = 'seller/' . $sellerId;
        // Ensure path stays under seller/<seller_id>
        if (strpos($folder, $baseFolder) !== 0) {
            $folder = $baseFolder . '/' . ltrim($folder, '/');
        }

        $mediaPath = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . $folder;
        if (is_dir($mediaPath)) {
            $it    = new \RecursiveDirectoryIterator($mediaPath, \FilesystemIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                // $logger->info('Removing ' . $file->getRealPath());
                // Remove image from SellerImages model if it matches
                $mediaBasePath = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR;
                $relativePath = ltrim(str_replace($mediaBasePath, '', $file->getRealPath()), '/\\');
                if (strpos($relativePath, 'seller/' . $sellerId) === 0) {
                    try {
                        /** @var \TCGCollective\MarketplaceProductImportExport\Model\SellerImagesFactory $modelFactory */
                        $modelFactory = \Magento\Framework\App\ObjectManager::getInstance()
                            ->get(\TCGCollective\MarketplaceProductImportExport\Model\SellerImagesFactory::class);
                        $collection = $modelFactory->create()->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->addFieldToFilter('path', $relativePath);
                        foreach ($collection as $image) {
                            $image->delete();
                        }
                    } catch (\Exception $e) {
                        $logger->err('Error deleting SellerImages: ' . $e->getMessage());
                    }
                }
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
            @rmdir($mediaPath);
        }

        return $result->setData(['success' => true]);
    }
}
