<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Export;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Action;

/**
 * Download history controller
 */
class Download extends Action implements HttpGetActionInterface
{
    protected $fileSystem;


    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
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
        // $fileName = basename($this->getRequest()->getParam('filename'));

        // /** @var \Magento\ImportExport\Helper\Report $reportHelper */
        // $reportHelper = $this->_objectManager->get(\Magento\ImportExport\Helper\Report::class);

        // if (!$reportHelper->importFileExists($fileName)) {
        //     /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        //     $resultRedirect = $this->resultRedirectFactory->create();
        //     $resultRedirect->setPath('*/history');
        //     return $resultRedirect;
        // }

        // $this->fileFactory->create(
        //     $fileName,
        //     null,
        //     DirectoryList::VAR_IMPORT_EXPORT,
        //     'application/octet-stream',
        //     $reportHelper->getReportSize($fileName)
        // );

        // /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        // $resultRaw = $this->resultRawFactory->create();
        // $resultRaw->setContents($reportHelper->getReportOutput($fileName));
        // return $resultRaw;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $this->fileSystem = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->fileSystem = $objectManager->get('\Magento\Framework\Filesystem'); 
        $this->fileFactory = $objectManager->get('\Magento\Framework\App\Response\Http\FileFactory'); 

        $fileName = $this->getRequest()->getParam('filename'); // Get file name from request
        if (!$fileName) {
            $this->messageManager->addErrorMessage(__('File not specified.'));
            return $this->_redirect('*/*/');
        }

        try {
            $varDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $filePath = $varDirectory->getAbsolutePath($fileName);

            if (!file_exists($filePath)) {
                $this->messageManager->addErrorMessage(__('The file does not exist.'));
                return $this->_redirect('*/*/');
            }

            return $this->fileFactory->create(
                basename($filePath),
                [
                    'type'  => 'filename',
                    'value' => $filePath,
                    'rm'    => false, // Set to true to remove file after download
                ],
                DirectoryList::VAR_DIR
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error occurred: %1', $e->getMessage()));
            return $this->_redirect('*/*/');
        }
    }
}
