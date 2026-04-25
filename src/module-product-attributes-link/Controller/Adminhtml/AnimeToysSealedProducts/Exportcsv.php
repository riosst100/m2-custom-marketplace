<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ActionToySealedProducts;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\ActionToySealedProducts\CollectionFactory;

class Exportcsv extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var LayoutInterface
     */
    protected $_layout;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;
    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $resultPageFactory;
    protected $mappingHelper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutInterface $layout
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutInterface $layout,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        parent::__construct($context,$resultPageFactory,$coreRegistry);
        $this->_layout = $layout;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->mappingHelper = $mappingHelper;
    }


    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $mainAttrCode = 'card_game';
        $name = 'card_game_sealed_products_export_'.date('Y-m-d').' '.date('H-i-s');
        $file = 'export/sealed_products/'. $name . '.csv';
        $allowedFields = [
            "images",
            "sc_release",
            "sc_release_jp",
            "sc_set_type",
            "sc_set_name",
            "name",
            "sc_feature",
            "sc_brand",
            "release_date",
            "release_year",
            "sc_card_manufacturer",
            "language",
            "bar_code",
            "bar_code_no",
            "age_level"
        ];

        $setsTableName = 'marketplace_card_games_sets';
        $setsFieldToSelet = [
            'card_set',
            $mainAttrCode
        ];

        $refererUrl = $this->_redirect->getRefererUrl();

        $setId = null;

        if ($refererUrl) {
            if (preg_match('/set_id\/(\d+)/', $refererUrl, $matches)) {
                $setId = $matches[1]; // The first capturing group contains the set_id
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($setId) {
            try {
                $headers = [];

                // init model and delete
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('set_id', $setId);
                $collection->addFieldToSelect($allowedFields);

                $collection->getSelect()->joinLeft(
                    ['sets' => $setsTableName],
                    'main_table.set_id=sets.entity_id',
                    $setsFieldToSelet
                );
                
                $params = [];
                foreach ($collection as $key => $model) {
                    $data = $model->getData();
                    if ($data) {
                        $params[] = $data;
                        $headers = array_keys($data);
                    }
                }

                $this->directory->create('export');
                $stream = $this->directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($headers);
                $rowData = [];
                $rowData[] = $headers;
                foreach ($params as $row) {
                    $imageName = $row['images'] ? basename($row['images']) : '';
                    $row['images'] = $imageName;
                    $cardGame = $row[$mainAttrCode] ?? null;
                    if ($cardGame) {
                        $row[$mainAttrCode] = $this->mappingHelper->getOptionLabelById($mainAttrCode, $cardGame);
                    }
                    $rowData[] = $row;
                    $stream->writeCsv($row);
                }
                $stream->unlock();
                $stream->close();

                $fullFilePath =  $this->directory->getAbsolutePath($file);
// dd($fullFilePath);
                // display success message
                $handle = fopen($fullFilePath, 'w');

                // Menambahkan BOM di awal
                fwrite($handle, "\xEF\xBB\xBF");

                // Menulis data produk ke file CSV
                foreach ($rowData as $row) {
                    fputcsv($handle, $row);
                }

                $file = [
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true  // can delete file after use
                ];

                
                
                return $this->fileFactory->create($name . '.csv', $file, 'var');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                
            }
        }

        // display error message
        $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));
        // go to grid
        return $resultRedirect->setPath($refererUrl);
    }
}
