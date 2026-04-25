<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceProductImportExport\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Psr\Log\LoggerInterface;

/**
 * Save media asset to the database
 */
class SaveAssets implements SaveAssetsInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $assets): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/directories.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('SaveAssets override'); 

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        // Format the trace into readable lines
        $formattedTrace = [];
        foreach ($backtrace as $trace) {
            $formattedTrace[] = (isset($trace['class']) ? $trace['class'] . '::' : '') .
                                $trace['function'] .
                                ' (line ' . ($trace['line'] ?? 'N/A') . ')';
        }

        // Log it
        // $logger->info("Function call backtrace:\n" . implode("\n", $formattedTrace));

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $sellerId = $this->helper->getSellerId();

        // $logger->info('SaveAssets sellerId: '.$sellerId); 

        $failedAssets = [];
        foreach ($assets as $asset) {
            try {
                $record = [
                    'id' => $asset->getId(),
                    'path' => $asset->getPath(),
                    'title' => $asset->getTitle(),
                    'description' => $asset->getDescription(),
                    'source' => $asset->getSource(),
                    'hash' => $asset->getHash(),
                    'content_type' => $asset->getContentType(),
                    'width' => $asset->getWidth(),
                    'height' => $asset->getHeight(),
                    'size' => $asset->getSize(),
                    'seller_id' => $sellerId ?? '',
                ];

                if ($asset->getCreatedAt()) {
                    $record['created_at'] = $asset->getCreatedAt();
                }

                if ($asset->getUpdatedAt()) {
                    $record['updated_at'] = $asset->getUpdatedAt();
                }

                $connection->insertOnDuplicate($tableName, $record);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedAssets[] = $asset;
            }
        }

        if (!empty($failedAssets)) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the media assets: %assets',
                    [
                        'assets' => implode(' ,', $failedAssets)
                    ]
                )
            );
        }
    }
}
