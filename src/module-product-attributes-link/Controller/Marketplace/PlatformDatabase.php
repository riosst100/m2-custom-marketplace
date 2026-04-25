<?php
declare(strict_types=1);

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;

abstract class PlatformDatabase extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \TCGCollective\PlatformDatabase\Helper\Data
     */
    protected $platformDbHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Registry $coreRegistry,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->platformDbHelper = $platformDbHelper;
        parent::__construct($context);
    }
}
