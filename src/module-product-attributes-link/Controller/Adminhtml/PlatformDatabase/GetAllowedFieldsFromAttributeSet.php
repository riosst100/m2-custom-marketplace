<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase;

class GetAllowedFieldsFromAttributeSet extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $registry;
    protected $jsonResultFactory;
    protected $resultPageFactory;
    protected $platformDatabaseHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDatabaseHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->platformDatabaseHelper = $platformDatabaseHelper;
        $this->jsonResultFactory = $jsonResultFactory;
    }
    
    public function execute()
    {
        $data = [];
        
        $data = $this->platformDatabaseHelper->getAllowedFieldsFromAttributeSet();
        
        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}
