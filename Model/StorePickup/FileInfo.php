<?php
/**
 * MageINIC
 * Copyright (C) 2023. MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_StorePickup
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\StorePickup\Model\StorePickup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\ExtendedDriverInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class FileInfo
 *
 * Provides information about requested file
 */
class FileInfo
{
    /**
     * Path in /pub/media directory
     */
    public const ENTITY_MEDIA_PATH = '/mageINIC/store_pickup';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Mime
     */
    private Mime $mime;

    /**
     * @var WriteInterface|null
     */
    private ?WriteInterface $mediaDirectory = null;

    /**
     * @var ReadInterface|null
     */
    private ?ReadInterface $baseDirectory = null;

    /**
     * @var ReadInterface|null
     */
    private ?ReadInterface $pubDirectory = null;

    /**
     * @var StoreManager
     */
    private StoreManager $storeManager;

    /**
     * FileInfo Constructor.
     *
     * @param Filesystem $filesystem
     * @param Mime $mime
     * @param StoreManager $storeManager
     */
    public function __construct(
        Filesystem   $filesystem,
        Mime         $mime,
        StoreManager $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     * @throws FileSystemException
     */
    public function getMimeType(string $fileName): string
    {
        if ($this->getMediaDirectory()->getDriver() instanceof ExtendedDriverInterface) {
            $fileName = $this->isBeginsWithMediaDirectoryPath($fileName)
                ? $fileName : 'media' . $this->getFilePath($fileName);
            return $this->mediaDirectory->getDriver()->getMetadata($fileName)['mimetype'];
        }
        return $this->mime->getMimeType($this->getMediaDirectory()->getAbsolutePath($this->getFilePath($fileName)));
    }

    /**
     * Retrieve WriteInterface instance
     *
     * @return WriteInterface|null
     * @throws FileSystemException
     */
    private function getMediaDirectory(): ?WriteInterface
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Construct and return file sub-path based on filename relative to media directory
     *
     * @param string $fileName
     * @return string
     * @throws FileSystemException
     */
    private function getFilePath(string $fileName): string
    {
        $filePath = $this->removeStorePath($fileName);
        $filePath = ltrim($filePath, '/');

        $mediaDirectoryRelativeSubPath = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath($filePath);
        $isFileNameBeginsWithMediaDirectoryPath = $this->isBeginsWithMediaDirectoryPath($fileName);

        // if the file is not using a relative path, it resides in the mageINIC/testimonial media directory
        $fileIsInCategoryMediaDir = !$isFileNameBeginsWithMediaDirectoryPath;

        return $fileIsInCategoryMediaDir
            ? self::ENTITY_MEDIA_PATH . '/' . $filePath
            : substr($filePath, strlen($mediaDirectoryRelativeSubPath));
    }

    /**
     * Clean store path in case if it's exists
     *
     * @param string $path
     * @return string
     */
    private function removeStorePath(string $path): string
    {
        $result = $path;
        try {
            $storeUrl = $this->storeManager->getStore()->getBaseUrl() ?? '';
        } catch (NoSuchEntityException $e) {
            return $result;
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $path = parse_url($path, PHP_URL_PATH);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $storePath = parse_url($storeUrl, PHP_URL_PATH);
        $storePath = rtrim($storePath, '/');

        return preg_replace('/^' . preg_quote($storePath, '/') . '/', '', $path);
    }

    /**
     * Retrieve media directory sub-path relative to base directory path
     *
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    private function getMediaDirectoryPathRelativeToBaseDirectoryPath(string $filePath = ''): string
    {
        $baseDirectory = $this->getBaseDirectory();
        $baseDirectoryPath = $baseDirectory->getAbsolutePath();
        $mediaDirectoryPath = $this->getMediaDirectory()->getAbsolutePath();
        $pubDirectoryPath = $this->getPubDirectory()->getAbsolutePath();

        $mediaDirectoryRelativeSubPath = substr($mediaDirectoryPath, strlen($baseDirectoryPath));
        $pubDirectory = $baseDirectory->getRelativePath($pubDirectoryPath);

        if ($pubDirectory && str_starts_with($mediaDirectoryRelativeSubPath, $pubDirectory)
            && !str_starts_with($filePath, $pubDirectory)) {
            $mediaDirectoryRelativeSubPath = substr($mediaDirectoryRelativeSubPath, strlen($pubDirectory));
        }

        return $mediaDirectoryRelativeSubPath;
    }

    /**
     * Retrieve Base Directory read instance
     *
     * @return ReadInterface|null
     */
    private function getBaseDirectory(): ?ReadInterface
    {
        if (!isset($this->baseDirectory)) {
            $this->baseDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        }

        return $this->baseDirectory;
    }

    /**
     * Retrieve Pub Directory read instance
     *
     * @return ReadInterface|null
     */
    private function getPubDirectory(): ?ReadInterface
    {
        if (!isset($this->pubDirectory)) {
            $this->pubDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        }

        return $this->pubDirectory;
    }

    /**
     * Checks for whether $fileName string begins with media directory path
     *
     * @param string $fileName
     * @return bool
     * @throws FileSystemException
     */
    public function isBeginsWithMediaDirectoryPath(string $fileName): bool
    {
        $filePath = $this->removeStorePath($fileName);
        $filePath = ltrim($filePath, '/');

        $mediaDirectoryRelativeSubPath = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath($filePath);
        return str_starts_with($filePath, (string)$mediaDirectoryRelativeSubPath);
    }

    /**
     * Retrieve file statistics data
     *
     * @param string $fileName
     * @return array
     * @throws FileSystemException
     */
    public function getStat(string $fileName): array
    {
        $filePath = $this->getFilePath($fileName);
        return $this->getMediaDirectory()->stat($filePath);
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     * @throws FileSystemException
     */
    public function isExist(string $fileName): bool
    {
        $filePath = $this->getFilePath($fileName);
        return $this->getMediaDirectory()->isExist($filePath);
    }

    /**
     * Retrieve file relative path to media directory
     *
     * @param string $filename
     * @return string
     * @throws FileSystemException
     */
    public function getRelativePathToMediaDirectory(string $filename): string
    {
        return $this->getFilePath($filename);
    }
}
