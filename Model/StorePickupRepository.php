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
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Route\Config;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterfaceFactory as StorePickupFactory;
use MageINIC\StorePickup\Api\Data\StorePickupSearchResultsInterfaceFactory as SearchResultsFactory;
use MageINIC\StorePickup\Api\StorePickupRepositoryInterface;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup as ResourceStorePickup;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory;

/**
 * Class for StorePickupRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorePickupRepository implements StorePickupRepositoryInterface
{
    /**
     * @var ResourceStorePickup
     */
    protected ResourceStorePickup $resource;

    /**
     * @var StorePickupFactory
     */
    protected StorePickupFactory $storePickupFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var SearchResultsFactory
     */
    protected SearchResultsFactory $searchResultsFactory;

    /**
     * @var DataObjectProcessor
     */
    protected DataObjectProcessor $dataObjectProcessor;

    /**
     * @var CollectionProcessor
     */
    protected CollectionProcessor $collectionProcessor;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Config|null
     */
    private ?Config $routeConfig = null;

    /**
     * @param ResourceStorePickup $resource
     * @param StorePickupFactory $storePickupFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessor $collectionProcessor
     * @param StoreManagerInterface $storeManager
     * @param Config|null $routeConfig
     */
    public function __construct(
        ResourceStorePickup   $resource,
        StorePickupFactory    $storePickupFactory,
        CollectionFactory     $collectionFactory,
        SearchResultsFactory  $searchResultsFactory,
        CollectionProcessor   $collectionProcessor,
        StoreManagerInterface $storeManager,
        ?Config               $routeConfig = null
    ) {
        $this->resource = $resource;
        $this->storePickupFactory = $storePickupFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->storeManager = $storeManager;
        $this->routeConfig = $routeConfig ?? ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @inheritdoc
     */
    public function save(StorePickupInterface $storePickup): StorePickupInterface
    {
        if (empty($storePickup->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $storePickup->setStoreId($storeId);
        }

        try {
            $this->validateRoutesDuplication($storePickup);
            $this->resource->save($storePickup);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the storePickUp: %1',
                $exception->getMessage()
            ));
        }
        return $storePickup;
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
    public function delete(StorePickupInterface $storePickup): bool
    {
        try {
            $this->resource->delete($storePickup);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the StorePickup: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): StorePickupInterface
    {
        $storePickup = $this->storePickupFactory->create();
        $this->resource->load($storePickup, $id);

        if (!$storePickup->getEntityId()) {
            throw new NoSuchEntityException(__('StorePickup with id "%1" does not exist.', $id));
        }
        return $storePickup;
    }

    /**
     * Checks that page identifier doesn't duplicate existed routes
     *
     * @param StorePickupInterface $storePickup
     * @return void
     * @throws CouldNotSaveException
     */
    private function validateRoutesDuplication(StorePickupInterface $storePickup): void
    {
        if ($this->routeConfig->getRouteByFrontName($storePickup->getIdentifier(), 'frontend')) {
            throw new CouldNotSaveException(
                __('The value specified in the URL Key field would generate a URL that already exists.')
            );
        }
    }
}
