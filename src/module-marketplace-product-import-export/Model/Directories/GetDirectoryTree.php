<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceProductImportExport\Model\Directories;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;

/**
 * Build media gallery folder tree structure by path
 */
class GetDirectoryTree
{
    private const XML_PATH_MEDIA_GALLERY_IMAGE_FOLDERS
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @param Filesystem $filesystem
     * @param IsPathExcludedInterface $isPathExcluded
     * @param ScopeConfigInterface|null $coreConfig
     */
    public function __construct(
        Filesystem $filesystem,
        IsPathExcludedInterface $isPathExcluded,
        ?ScopeConfigInterface $coreConfig = null
    ) {
        $this->filesystem = $filesystem;
        $this->isPathExcluded = $isPathExcluded;
        $this->coreConfig = $coreConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Return directory folder structure in array
     *
     * @return array
     * @throws ValidatorException
     */
    public function execute(): array
    {
        $tree = [
            'name' => 'root',
            'path' => '/',
            'children' => []
        ];
        $directories = $this->getDirectories();
        foreach ($directories as $idx => &$node) {
            $node['children'] = [];
            $result = $this->findParent($node, $tree);
            $parent = &$result['treeNode'];

            $parent['children'][] = &$directories[$idx];
        }
        return $tree['children'];
    }

    /**
     * Build directory tree array in format for jstree strandart
     *
     * @return array
     * @throws ValidatorException
     */
    private function getDirectories(): array
    {
    	$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/directories.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('getDirectories'); 

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $sellerId = $this->helper->getSellerId();

        // $logger->info('Seller Id: ' . $sellerId);
        $directories = [];

        /** @var Read $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        // $logger->info('Media dir: '.json_encode($mediaDirectory)); 

        if ($mediaDirectory->isDirectory()) {
            $imageFolderPaths = $this->coreConfig->getValue(
                self::XML_PATH_MEDIA_GALLERY_IMAGE_FOLDERS,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
            if (!empty($sellerId)) {
                // $logger->info('Seller Id: '.$sellerId); 
                $sellerFolders = $this->getSellerFolders($sellerId);
                // $logger->info('Seller Folders: '.json_encode($sellerFolders)); 
                $imageFolderPaths = [];
                $i = 0;
                foreach ($sellerFolders as $folder) {
                    $imageFolderPaths['seller_images_folder_'.$i] = $folder;
                    // $imageFolderPaths['seller_images_folder'] = "seller/" . $sellerId ."/Root Folder";
                    $i++;
                }                
            }
            
            $logger->info('imageFolderPaths: ' . json_encode($imageFolderPaths)); 
            sort($imageFolderPaths);

            foreach ($imageFolderPaths as $imageFolderPath) {
                $imageDirectory = $this->filesystem->getDirectoryReadByPath(
                    $mediaDirectory->getAbsolutePath($imageFolderPath)
                );
                if ($imageDirectory->isDirectory()) {
                    $directories[] = $this->getDirectoryData($imageFolderPath);
                    foreach ($imageDirectory->readRecursively() as $path) {
                        if ($imageDirectory->isDirectory($path)) {
                            $directories[] = $this->getDirectoryData(
                                $mediaDirectory->getRelativePath($imageDirectory->getAbsolutePath($path))
                            );
                        }
                    }
                }
            }
        }
        // $logger->info(json_encode($directories)); 
        return $directories;
    }

    /**
     * Return jstree data for given path
     *
     * @param string $path
     * @return array
     */
    private function getDirectoryData(string $path): array
    {
        $pathArray = explode('/', $path);
        return [
            'text' => count($pathArray) > 0 ? end($pathArray) : $path,
            'id' => $path,
            'li_attr' => ['data-id' => $path],
            'path' => $path,
            'path_array' => $pathArray
        ];
    }

    /**
     * Find parent directory
     *
     * @param array $node
     * @param array $treeNode
     * @param int $level
     * @return array
     */
    private function findParent(array &$node, array &$treeNode, int $level = 0): array
    {
        $nodePathLength = count($node['path_array']);
        $treeNodeParentLevel = $nodePathLength - 1;

        $result = ['treeNode' => &$treeNode];

        if ($nodePathLength <= 1 || $level > $treeNodeParentLevel) {
            return $result;
        }

        foreach ($treeNode['children'] as &$tnode) {
            $tNodePathLength = count($tnode['path_array']);
            $found = false;
            while ($level < $tNodePathLength) {
                $found = $node['path_array'][$level] === $tnode['path_array'][$level];
                if ($found) {
                    $level ++;
                } else {
                    break;
                }
            }
            if ($found) {
                return $this->findParent($node, $tnode, $level);
            }
        }
        return $result;
    }

    public function getSellerFolders($sellerId)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $sellerBasePath = "seller/{$sellerId}";
        $folders = [];

        foreach ($mediaDirectory->readRecursively($sellerBasePath) as $path) {
            if ($mediaDirectory->isDirectory($path)) {
                $relativePath = str_replace('\\', '/', $path); // normalize slashes
                $parts = explode('/', $relativePath);

                // keep only folders that have exactly 3 segments: seller / {id} / {folder}
                if (count($parts) === 3) {
                    $folders[] = $relativePath;
                }
            }
        }

        return $folders;
    }
}
