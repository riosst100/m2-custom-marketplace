<?php
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlInterface;

class ListImages extends Action
{
    protected $resultJsonFactory;
    protected $sellerFactory;
    protected $customerSession;
    protected $resource;
    protected $url;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SellerFactory $sellerFactory,
        CustomerSession $customerSession,
        ResourceConnection $resource,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sellerFactory     = $sellerFactory;
        $this->customerSession   = $customerSession;
        $this->resource          = $resource;
        $this->url               = $url;
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

        // --- Folder path handling ---
        $relativeFolder = trim($this->getRequest()->getParam('folder', ''));
        $dbPrefix       = 'seller/' . $sellerId;

        $dbFolder = $dbPrefix;
        if ($relativeFolder !== '') {
            $dbFolder .= '/' . ltrim($relativeFolder, '/');
        }

        // --- Optional filters from request ---
        $nameFilter     = trim((string) $this->getRequest()->getParam('name', ''));
        $categoryFilter = trim((string) $this->getRequest()->getParam('category', ''));

        $conn  = $this->resource->getConnection();
        $table = $this->resource->getTableName('marketplace_seller_images');

        $select = $conn->select()
            ->from($table, ['entity_id', 'path', 'title', 'created_at'])
            ->where('seller_id = ?', $sellerId)
            ->where('path LIKE ?', $dbFolder . '/%')
            ->order('created_at DESC');

        // --- Apply filters if present ---
        if ($nameFilter !== '') {
            // search by image title (adjust column if different)
            $select->where('title LIKE ?', '%' . $nameFilter . '%');
        }

        if ($categoryFilter !== '') {
            // only if you have a categories (or similar) column
            $select->where('categories = ?', $categoryFilter);
        }

        $rows = $conn->fetchAll($select);

        $baseUrl = rtrim($this->url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]), '/');

        foreach ($rows as &$r) {
            $r['url'] = $baseUrl . '/' . ltrim($r['path'], '/');
        }

        return $result->setData([
            'success' => true,
            'images'  => $rows
        ]);
    }
}
