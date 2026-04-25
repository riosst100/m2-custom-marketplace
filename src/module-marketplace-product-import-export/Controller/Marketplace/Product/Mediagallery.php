<?php
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Mediagallery extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_fileio;
    protected $_filesystem;
    protected $sellerFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $fileio,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_filesystem = $filesystem;
        $this->_fileio = $fileio;
        $this->sellerFactory = $sellerFactory;
        $this->customerSession = $customerSession;
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
        // Just render the page; JS will fetch folders/images via AJAX
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Images'));

        // Check root folders
        $rootFolderPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
        ->getAbsolutePath('seller/6/Root Folder');

        // dd($rootFolderPath);

        if (!is_dir($rootFolderPath)) {
            try {
            $this->_fileio->checkAndCreateFolder($rootFolderPath, 0777);
            // $this->_fileio->chmod($rootFolderPath, '0777', true);
            } catch(\Exception $e) {
                // dd($e);
            }
        }

        return $resultPage;
    }
}
