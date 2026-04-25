<?php
declare(strict_types=1);

namespace TCGCollective\PlatformDatabase\Controller\Adminhtml;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

abstract class Index extends \Magento\Backend\App\Action
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
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    protected $mainCategoriesConfigFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigFactory $mainCategoriesConfigFactory,
        Filesystem $filesystem,
        FileFactory $fileFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->platformDbHelper = $platformDbHelper;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->mainCategoriesConfigFactory = $mainCategoriesConfigFactory;
        parent::__construct($context);
    }
}
