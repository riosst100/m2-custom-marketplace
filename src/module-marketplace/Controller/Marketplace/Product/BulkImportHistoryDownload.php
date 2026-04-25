<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TCGCollective\MarketPlace\Controller\Marketplace\Product;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Action;

/**
 * Download history controller
 */
class BulkImportHistoryDownload extends Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Download backup action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function execute()
    {
        $fileName = basename($this->getRequest()->getParam('filename'));

        /** @var \Magento\ImportExport\Helper\Report $reportHelper */
        $reportHelper = $this->_objectManager->get(\Magento\ImportExport\Helper\Report::class);

        if (!$reportHelper->importFileExists($fileName)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('catalog/product/import');
            return $resultRedirect;
        }

        $file = [
            'type' => 'filename',
            'value' => $reportHelper->getReportAbsolutePath($fileName),
            'rm' => false
        ];

        return $this->fileFactory->create($fileName, $file, 'var');
    }
}
