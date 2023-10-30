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

namespace MageINIC\StorePickup\Model\ResourceModel;

use Exception;
use MageINIC\StorePickup\Api\Data\HolidaysInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use MageINIC\StorePickup\Model\StorePickup\UrlPathGenerator;
use MageINIC\StorePickup\Model\StorePickup\UrlRewriteGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * class for StorePickup Resource Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorePickup extends AbstractDb
{
    /**
     * @var StoreInterface|null
     */
    protected ?StoreInterface $_store = null;

    /**
     * @var HolidaysInterface|null
     */
    protected ?HolidaysInterface $_holiday = null;

    /**
     * @var StoreManager
     */
    protected StoreManager $_storeManager;

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var EntityManager
     */
    protected EntityManager $entityManager;

    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /**
     * @var UrlPathGenerator
     */
    protected UrlPathGenerator $urlPathGenerator;

    /**
     * @var HolidaysRepository
     */
    private HolidaysRepository $holidaysRepository;

    /**
     * @var UrlPersistInterface
     */
    protected UrlPersistInterface $urlPersist;

    /**
     * StorePickup Constructor.
     *
     * @param Context $context
     * @param StoreManager $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param HolidaysRepository $holidaysRepository
     * @param UrlPathGenerator $urlPathGenerator
     * @param UrlPersistInterface $urlPersist
     * @param string $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context             $context,
        StoreManager        $storeManager,
        DateTime            $dateTime,
        EntityManager       $entityManager,
        MetadataPool        $metadataPool,
        ResourceConnection  $resourceConnection,
        HolidaysRepository  $holidaysRepository,
        UrlPathGenerator    $urlPathGenerator,
        UrlPersistInterface $urlPersist,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->holidaysRepository = $holidaysRepository;
        $this->urlPathGenerator = $urlPathGenerator;
        $this->urlPersist = $urlPersist;
        parent::__construct($context, $connectionName);
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int|null $isActive
     * @return Select
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _getLoadByIdentifierSelect(string $identifier, $store, int $isActive = null): Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()
            ->from(['msp' => $this->getMainTable()])
            ->join(
                ['msps' => $this->getTable('mageinic_store_pickup_store')],
                'msp.' . $linkField . ' = msps.' . $linkField,
                []
            )
            ->where('msp.identifier = ?', $identifier)
            ->where('msps.store_id IN (?)', $store);

        if ($isActive !== null) {
            $select->where('msp.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     * Get Connection.
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(StorePickupInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string|null $field field to load by (defaults to model id)
     * @return $this
     * @throws Exception
     */
    public function load(AbstractModel $object, $value, $field = null): StorePickup
    {
        $id = $this->getStorePickupId($object, $value, $field);
        if ($id) {
            $this->entityManager->load($object, $id);
        }
        return $this;
    }

    /**
     * Retrieve StorePickup ID.
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string|null $field
     * @return mixed
     * @throws Exception
     */
    private function getStorePickupId(AbstractModel $object, mixed $value, string $field = null): mixed
    {
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'entity_id';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $id = $value;
        /** @var StorePickupInterface $object */
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $id = count($result) ? $result[0] : false;
        }
        return $id;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     * @throws LocalizedException
     * @throws Exception
     */
    protected function _getLoadSelect($field, $value, $object): Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        /** @var StorePickupInterface $object */
        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId()
            ];
            $select->join(
                ['msps' => $this->getTable('mageinic_store_pickup_store')],
                $this->getMainTable() . '.' . $linkField . ' = msps.' . $linkField,
                []
            )
                ->where('is_active = ?', 1)
                ->where('msps.store_id IN (?)', $storeIds)
                ->order('msps.store_id DESC')
                ->limit(1);
        }

        /** @var StorePickupInterface $object */
        if ($object->getHolidays()) {
            $holidayIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getHolidays()
            ];
            $select->join(
                ['msph' => $this->getTable('mageinic_store_pickup_holidays')],
                $this->getMainTable() . '.' . $linkField . ' = msph.' . $linkField,
                []
            )
                ->where('is_active = ?', 1)
                ->where('msps.holiday_id IN (?)', $holidayIds)
                ->order('msps.holiday_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function lookupStoreIds(int $id): array
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['msps' => $this->getTable('mageinic_store_pickup_store')], 'store_id')
            ->join(
                ['msp' => $this->getMainTable()],
                'msps.' . $linkField . ' = msp.' . $linkField,
                []
            )
            ->where('msp.' . $entityMetadata->getIdentifierField() . ' = :entity_id');

        return $connection->fetchCol($select, ['entity_id' => (int)$id]);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function lookupHolidayIds(int $id): array
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['msph' => $this->getTable('mageinic_store_pickup_holidays')], 'holiday_id')
            ->join(
                ['msp' => $this->getMainTable()],
                'msph.' . $linkField . ' = msp.' . $linkField,
                []
            )
            ->where('msp.' . $entityMetadata->getIdentifierField() . ' = :entity_id');

        return $connection->fetchCol($select, ['entity_id' => $id]);
    }

    /**
     * Retrieve Store.
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * Set Store.
     *
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore(StoreInterface $store): StorePickup
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve Holiday.
     *
     * @return HolidaysInterface
     * @throws LocalizedException
     */
    public function getHoliday(): HolidaysInterface
    {
        return $this->holidaysRepository->getbyId($this->_holiday->getId());
    }

    /**
     * Set Holiday.
     *
     * @param HolidaysInterface $holiday
     * @return $this
     */
    public function setHoliday(HolidaysInterface $holiday): StorePickup
    {
        $this->_holiday = $holiday;
        return $this;
    }

    /**
     * Save an object.
     *
     * @param AbstractModel $object
     * @return $this
     * @throws Exception
     */
    public function save(AbstractModel $object): StorePickup
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * Delete the object
     *
     * @param AbstractModel $object
     * @return $this
     * @throws Exception
     */
    public function delete(AbstractModel $object): StorePickup
    {
        $this->entityManager->delete($object);
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init('mageinic_store_pickup', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(AbstractModel $object): StorePickup
    {
        /** @var StorePickupInterface $object */
        if (!$this->isValidIdentifier($object)) {
            throw new LocalizedException(
                __(
                    "The Store Pickup URL key can't use capital letters or disallowed symbols. "
                    . "Remove the letters and symbols and try again."
                )
            );
        }

        if ($this->isNumericIdentifier($object)) {
            throw new LocalizedException(
                __("The Store Pickup URL key can't use only numbers. Add letters or words and try again.")
            );
        }

        $urlKey = $object->getIdentifier();
        if ($urlKey === '' || $urlKey === null) {
            $object->setIdentifier($this->urlPathGenerator->generateUrlKey($object));
        }

        if (!$this->getIsUniquePickupToStores($object)) {
            throw new LocalizedException(
                __('A Store Pickup URL key with the same properties already exists in the selected store.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     *  Check whether identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidIdentifier(AbstractModel $object): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier') ?? '');
    }

    /**
     *  Check whether identifier is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericIdentifier(AbstractModel $object): bool
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier') ?? '');
    }

    /**
     * Check for unique of identifier of store pickup to selected store(s).
     *
     * @param AbstractModel $object
     * @return bool
     * @throws Exception
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsUniquePickupToStores(AbstractModel $object): bool
    {
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $stores = $this->_storeManager->isSingleStoreMode()
            ? [Store::DEFAULT_STORE_ID]
            : (array)$object->getData('store_id');

        $select = $this->getConnection()->select()
            ->from(['msp' => $this->getMainTable()])
            ->join(
                ['msps' => $this->getTable('mageinic_store_pickup_store')],
                'msp.' . $linkField . ' = msps.' . $linkField,
                []
            )
            ->where('msp.identifier = ?  ', $object->getData('identifier'))
            ->where('msps.store_id IN (?)', $stores);

        if ($object->getId()) {
            $select->where('msp.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }
}
