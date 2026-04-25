<?php
namespace TCGCollective\MarketplaceProductPreview\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;

class Preview extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;
    protected $productPreviewTokenFactory;


    /**
     * @var
     */
    protected $currentSeller = null;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory
     */
    protected $prdAttrLinkCollFactory;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->prdAttrLinkCollFactory = $prdAttrLinkCollFactory;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $this->productPreviewTokenFactory = $objectManager->create(\TCGCollective\MarketplaceProductPreview\Model\ProductPreviewTokenFactory::class);

        $paramData = $this->getRequest()->getParam('data');
        $paramData = $paramData['product'] ?? [];

        try {
            if ($paramData) {
                $productId = $paramData['current_product_id'] ?? null;
                $sellerId = $paramData['seller_id'] ?? null;
                // $sellerId = $this->getSeller()->getId() ?? null;
                // die($sellerId);
                if ($productId && $sellerId) {
                    $token = null;

                    $collection = $this->productPreviewTokenFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('seller_id', $sellerId);
                    if ($collection->getSize()) {
                        $token = $collection->getFirstItem()->getData('token');
                    } else {
                        $token = md5(uniqid(rand(), true));

                        $model = $this->productPreviewTokenFactory->create();
                        $model->setProductId($productId);
                        $model->setSellerId($sellerId);
                        $model->setToken($token);
                        $model->save();
                    }

                    if ($token) {
                        $pwaUrl = 'https://preprod.tcgcollective.co/sg';

                        $result = [
                            'url' => $pwaUrl.'/'.$paramData['url_key'].'.html?preview='.$token
                        ];

                        $resultJson->setData(['success' => true, 'data' => $result]);
                        return $resultJson;
                    }
                }
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        $resultJson->setData(['success' => false, 'data' => []]);
        return $resultJson;
    }
}