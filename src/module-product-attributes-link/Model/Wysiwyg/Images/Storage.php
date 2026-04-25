<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace TCGCollective\ProductAttributesLink\Model\Wysiwyg\Images;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Wysiwyg Images model.
 *
 * Tightly connected with controllers responsible for managing files so it uses session and is (sort of) a part
 * of the presentation layer.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @api
 * @since 100.0.2
 */
class Storage extends \Magento\Cms\Model\Wysiwyg\Images\Storage
{
    public const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';

    public const THUMBS_DIRECTORY_NAME = '.thumbs';

    public const THUMB_PLACEHOLDER_PATH_SUFFIX = 'Magento_Cms::images/placeholder_thumbnail.jpg';

    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * Config object
     *
     * @var \Magento\Framework\App\Config\Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $_cmsWysiwygImages = null;

    /**
     * @var array
     */
    protected $_resizeParameters;

    /**
     * @var array
     */
    protected $_extensions;

    /**
     * @var array
     */
    protected $_dirs;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory
     */
    protected $_directoryDatabaseFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\FileFactory
     */
    protected $_storageFileFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory
     */
    protected $_storageCollectionFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem\Io\File|null
     */
    private $ioFile;

    /**
     * @var \Magento\Framework\File\Mime|null
     */
    private $mime;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @var string
     */
    private $allowedPathPattern;

    /**
     * @var array
     */
    private $allowedDirs;

    /**
     * Construct
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory
     * @param \Magento\MediaStorage\Model\File\Storage\FileFactory $storageFileFactory
     * @param \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param array $resizeParameters
     * @param array $extensions
     * @param array $dirs
     * @param array $data
     * @param \Magento\Framework\Filesystem\DriverInterface $file
     * @param \Magento\Framework\Filesystem\Io\File|null $ioFile
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param \Magento\Framework\File\Mime $mime
     * @param ScopeConfigInterface $coreConfig
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Cms\Helper\Wysiwyg\Images $cmsWysiwygImages,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory,
        \Magento\MediaStorage\Model\File\Storage\FileFactory $storageFileFactory,
        \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        array $resizeParameters = [],
        array $extensions = [],
        array $dirs = [],
        array $data = [],
        \Magento\Framework\Filesystem\DriverInterface $file = null,
        \Magento\Framework\Filesystem\Io\File $ioFile = null,
        \Psr\Log\LoggerInterface $logger = null,
        \Magento\Framework\File\Mime $mime = null,
        ScopeConfigInterface $coreConfig = null
    ) {
        $this->_session = $session;
        $this->_backendUrl = $backendUrl;
        $this->_cmsWysiwygImages = $cmsWysiwygImages;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        $this->_assetRepo = $assetRepo;
        $this->_storageCollectionFactory = $storageCollectionFactory;
        $this->_storageFileFactory = $storageFileFactory;
        $this->_storageDatabaseFactory = $storageDatabaseFactory;
        $this->_directoryDatabaseFactory = $directoryDatabaseFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $this->_resizeParameters = $resizeParameters;
        $this->_extensions = $extensions;
        $this->_dirs = $dirs;
        $this->file = $file ?: ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Driver\File::class);
        $this->ioFile = $ioFile ?: ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Io\File::class);
        $this->mime = $mime ?: ObjectManager::getInstance()->get(\Magento\Framework\File\Mime::class);
        $this->coreConfig = $coreConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        parent::__construct(
            $session,
            $backendUrl,
            $cmsWysiwygImages,
            $coreFileStorageDb,
            $filesystem,
            $imageFactory,
            $assetRepo,
            $storageCollectionFactory,
            $storageFileFactory,
            $storageDatabaseFactory,
            $directoryDatabaseFactory,
            $uploaderFactory,
            $resizeParameters,
            $extensions,
            $dirs,
            $data,
            $file,
            $ioFile,
            $logger,
            $mime,
            $coreConfig
        );
        $this->initStorage();
    }

    /**
     * Initialize storage by creating wysiwyg image folders
     *
     * @return void
     */
    private function initStorage(): void
    {
        $imageFolders = $this->coreConfig->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        foreach ($imageFolders as $folder) {
            try {
                $this->_directory->create($folder);
            } catch (LocalizedException $e) {
                $this->logger->error(
                    sprintf("Creating media gallery image folder %s caused error: %s", $folder, $e->getMessage())
                );
            }
        }
    }

    /**
     * Thumbnail path getter
     *
     * @param string $filePath original file path
     * @param bool $checkFile OPTIONAL is it necessary to check file availability
     * @return string|false
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getThumbnailPath($filePath, $checkFile = false)
    {
        $mediaRootDir = $this->_cmsWysiwygImages->getStorageRoot();

        if ($filePath !== null && strpos($filePath, $mediaRootDir) === 0) {
            $relativeFilePath = substr($filePath, strlen($mediaRootDir));
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $thumbPath = $relativeFilePath === basename($filePath)
                ? $this->getThumbnailRoot() . DIRECTORY_SEPARATOR . $relativeFilePath
                : $this->getThumbnailRoot() . DIRECTORY_SEPARATOR . $relativeFilePath;

            if (!$checkFile || $this->_directory->isExist($this->_directory->getRelativePath($thumbPath))) {
                return $thumbPath;
            }
        }

        return false;
    }

    /**
     * Return width height for the image resizing.
     *
     * @param string $source
     * @return array
     */
    private function getResizedParams(string $source): array
    {
        $configWidth = $this->_resizeParameters['width'];
        $configHeight = $this->_resizeParameters['height'];

        $driver = $this->_directory->getDriver();
        [$imageWidth, $imageHeight] = getimagesizefromstring($driver->fileGetContents($source));

        if ($imageWidth && $imageHeight) {
            $imageWidth = $configWidth > $imageWidth ? $imageWidth : $configWidth;
            $imageHeight = $configHeight > $imageHeight ? $imageHeight : $configHeight;

            return  [$imageWidth, $imageHeight];
        }
        return [$configWidth, $configHeight];
    }

    /**
     * Prepare mime types config settings.
     *
     * @param string|null $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    private function getAllowedMimeTypes($type = null): array
    {
        $allowed = $this->getExtensionsList($type);

        return array_values(array_filter($allowed));
    }

    /**
     * Get list of allowed file extensions with mime type in values.
     *
     * @param string|null $type
     * @return array
     */
    private function getExtensionsList($type = null): array
    {
        if (is_string($type) && array_key_exists("{$type}_allowed", $this->_extensions)) {
            $allowed = $this->_extensions["{$type}_allowed"];
        } else {
            $allowed = $this->_extensions['allowed'];
        }

        return $allowed;
    }

    /**
     * Check if directory is allowed
     *
     * @param string $directoryPath Absolute path to a directory
     * @return bool
     */
    private function isDirectoryAllowed($directoryPath): bool
    {
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $storageRootLength = strlen($storageRoot);
        $mediaSubPathname = $directoryPath === null ? '' : substr($directoryPath, $storageRootLength);
        if (!$mediaSubPathname) {
            return false;
        }
        $mediaSubPathname = ltrim($mediaSubPathname, '/');
        return preg_match($this->getAllowedPathPattern(), $mediaSubPathname) == 1;
    }

    /**
     * Get allowed path pattern
     *
     * @return string
     */
    private function getAllowedPathPattern()
    {
        if (null === $this->allowedPathPattern) {
            $mediaGalleryImageFolders = $this->coreConfig->getValue(
                self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
                'default'
            );
            $regExp = '/^(';
            $or = '';
            foreach ($mediaGalleryImageFolders as $folder) {
                $folderPattern = str_replace('/', '[\/]+', $folder ?? '');
                $regExp .= $or . $folderPattern . '\b(?!-)(?:\/?[a-zA-Z0-9\-\_]+)*\/?$';
                $or = '|';
            }
            $regExp .= ')/';
            $this->allowedPathPattern = $regExp;
        }
        return $this->allowedPathPattern;
    }

    /**
     * Get allowed media gallery image folders
     *
     * Example:
     *   [
     *     [0 => 'wysiwyg'],
     *     [0 => 'catalog', 1 => 'category']
     *   ];
     *
     * @return array
     */
    private function getAllowedDirs(): array
    {
        if (null == $this->allowedDirs) {
            $imageFolders = $this->coreConfig->getValue(
                self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
                'default'
            );

            $this->allowedDirs = [];
            foreach ($imageFolders as $folder) {
                $this->allowedDirs[] = explode('/', $folder ?? '');
            }
        }
        return $this->allowedDirs;
    }

    /**
     * Get allowed dir mask.
     *
     * @param string $path
     * @return string
     */
    private function getAllowedDirMask(string $path)
    {
        $allowedDirs = $this->getAllowedDirs();
        // subfolder level under storage root
        $subfolderLevel = 1;
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $storageRootLength = strlen($storageRoot);
        $mediaSubPathname = substr($path, $storageRootLength);
        // Filter out the irrelevant allowed dirs for the path from the $allowedDirs array
        if ($mediaSubPathname) {
            $pathSegments = explode('/', trim($mediaSubPathname, '/'));
            foreach ($pathSegments as $index => $pathSegment) {
                // Find indexes of the relevant allowed dirs based on the path segment
                $subDirKeys = array_keys(array_column($allowedDirs, $index), $pathSegment);
                $dirs = [];
                // Rebuild the allowed dirs based on the found indexes
                foreach ($subDirKeys as $subDirKey) {
                    $dirs[] = $allowedDirs[$subDirKey];
                }
                $allowedDirs = $dirs;
                $subfolderLevel++;
            }
        }

        return '/^(' . implode('|', array_unique(array_column($allowedDirs, $subfolderLevel - 1))) . ')$/';
    }
}
