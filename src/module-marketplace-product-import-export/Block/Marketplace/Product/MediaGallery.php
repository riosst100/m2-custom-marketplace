<?php
namespace TCGCollective\MarketplaceProductImportExport\Block\Marketplace\Product;

use Magento\Framework\View\Element\Template;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Customer\Model\Session as CustomerSession;

class MediaGallery extends Template
{
    protected $sellerFactory;
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        SellerFactory $sellerFactory,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sellerFactory = $sellerFactory;
        $this->customerSession = $customerSession;
    }

    public function getSellerId()
    {
        $customerId = $this->customerSession->getId();
        if (!$customerId) return 0;
        $collection = $this->sellerFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setPageSize(1);
        $item = $collection->getFirstItem();
        return (int)$item->getData('seller_id');
    }

    public function getAjaxUrls()
    {
        $base = $this->getUrl('marketplace/catalog/product', ['_secure' => true]);
        return [
            'list_folders' => $this->getUrl('catalog/product/listfolders'),
            'list_images'  => $this->getUrl('catalog/product/listimages'),
            'list_categories'  => $this->getUrl('catalog/product/listcategories'),
            'create_folder'=> $this->getUrl('catalog/product/createfolder'),
            'delete_folder'=> $this->getUrl('catalog/product/deletefolder'),
            'upload'       => $this->getUrl('catalog/product/uploadimage')
        ];
    }
}
