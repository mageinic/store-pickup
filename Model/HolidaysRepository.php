<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
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
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\StorePickup\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use MageINIC\StorePickup\Api\Data\HolidaysInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as CollectionProcessor;
use MageINIC\StorePickup\Api\Data\HolidaysSearchResultsInterfaceFactory as SearchResultsFactory;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface;
use MageINIC\StorePickup\Model\ResourceModel\Holidays as ResourceHolidays;
use MageINIC\StorePickup\Model\ResourceModel\Holidays\CollectionFactory as CollectionFactory;

/**
 * Class for StoreHolidaysRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HolidaysRepository implements HolidaysRepositoryInterface
{
    /**
     * @var ResourceHolidays
     */
    protected ResourceHolidays $resource;

    /**
     * @var HolidaysFactory
     */
    protected HolidaysFactory $holidaysFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var SearchResultsFactory
     */
    protected SearchResultsFactory $searchResultsFactory;

    /**
     * @var CollectionProcessor
     */
    protected CollectionProcessor $collectionProcessor;

    /**
     * @var StoreManager
     */
    protected StoreManager $storeManager;

    /**
     * HolidaysRepository Constructor.
     *
     * @param ResourceHolidays $resource
     * @param HolidaysFactory $holidaysFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessor $collectionProcessor
     * @param StoreManager $storeManager
     */
    public function __construct(
        ResourceHolidays     $resource,
        HolidaysFactory      $holidaysFactory,
        CollectionFactory    $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessor  $collectionProcessor,
        StoreManager         $storeManager
    ) {
        $this->resource = $resource;
        $this->holidaysFactory = $holidaysFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function save(HolidaysInterface $holidays): HolidaysInterface
    {
        try {
            $this->resource->save($holidays);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the Holiday: %1', $exception->getMessage()));
        }

        return $holidays;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @inheritdoc
     */
    public function delete(HolidaysInterface $holidays): bool
    {
        try {
            $this->resource->delete($holidays);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the Holiday: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): HolidaysInterface
    {
        $holidays = $this->holidaysFactory->create();
        $this->resource->load($holidays, $id);
        if (!$holidays->getId()) {
            throw new NoSuchEntityException(
                __('Holidays with id "%1" does not exist.', $id)
            );
        }
        return $holidays;
    }
}
