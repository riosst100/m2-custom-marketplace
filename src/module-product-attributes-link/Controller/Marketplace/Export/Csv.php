<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Export;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;

class Csv extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper
     */
    protected $platformDatabaseHelper;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param \TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper $platformDatabaseHelper
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        \TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper $platformDatabaseHelper,
        FileFactory $fileFactory
    ) {
        $this->platformDatabaseHelper = $platformDatabaseHelper;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $result = $this->platformDatabaseHelper->downloadPlatformDatabaseAsCsvForBulkUpload();
        if ($result['success']) {
            return $this->fileFactory->create($result['name'], $result['file'], 'var');
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($result['refererUrl']);
    }
}
