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

use MageINIC\StorePickup\Model\ResourceModel\StorePickup\Collection;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory;
use MageINIC\StorePickup\Model\StorePickup;
use Magento\Framework\App\Request\DataPersistorInterface as DataPersistor;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\SerializerInterface as Serializer;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class for DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;
    /**
     * @var Image
     */
    protected Image $storePickupImage;
    /**
     * @var FileInfo
     */
    protected FileInfo $fileInfo;
    /**
     * @var File
     */
    protected File $fileSystemIo;
    /**
     * @var DataPersistor
     */
    private DataPersistor $dataPersistor;
    /**
     * @var array|null
     */
    private ?array $loadedData = null;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * DataProvider Constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistor $dataPersistor
     * @param Image $storePickupImage
     * @param FileInfo $fileInfo
     * @param File $fileSystemIo
     * @param Serializer $serializer
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistor $dataPersistor,
        Image $storePickupImage,
        FileInfo $fileInfo,
        File $fileSystemIo,
        Serializer $serializer,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storePickupImage = $storePickupImage;
        $this->fileInfo = $fileInfo;
        $this->fileSystemIo = $fileSystemIo;
        $this->serializer = $serializer;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * Retrieve Data
     *
     * @return array
     * @throws NoSuchEntityException|FileSystemException|LocalizedException
     */
    public function getData(): array
    {
        if (!isset($this->loadedData)) {
            $items = $this->collection->getItems();

            /** @var StorePickup $model */
            foreach ($items as $model) {
                $fileName = $model->getData('image');
                $storeSchedule = $model->getData('schedule');
                if ($this->fileInfo->isExist($fileName)) {
                    $fileIo = $this->fileSystemIo->getPathInfo($fileName);
                    $stat = $this->fileInfo->getStat($fileName);
                    $mime = $this->fileInfo->getMimeType($fileName);
                    $img = [
                        'image' => $fileName,
                        'name' => $fileIo['basename'],
                        'url' => $this->storePickupImage->getUrl($model),
                        'size' => $stat['size'],
                        'type' => $mime,
                    ];
                    $model->setData('image', [$img]);
                }

                if ($storeSchedule != '' && is_string($storeSchedule)) {
                    $schedule = $this->serializer->unserialize($storeSchedule);
                    $model->setData('schedule', $schedule);
                }

                $this->loadedData[$model->getId()] = $model->getData();
            }

            $data = $this->dataPersistor->get('mageinic_store_pickup');
            if (!empty($data)) {
                $model = $this->collection->getNewEmptyItem();
                $model->setData($data);
                $this->loadedData[$model->getId()] = $model->getData();
                $this->dataPersistor->clear('mageinic_store_pickup');
            }
            $this->loadedData ??= [];
        }

        return $this->loadedData;
    }
}
