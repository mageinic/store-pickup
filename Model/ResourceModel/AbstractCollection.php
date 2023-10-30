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

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface as Adapter;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface as Manager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as DbAbstractCollection;
use Magento\Store\Model\Store;
use MageINIC\StorePickup\Model\Holidays;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Abstract collection of StorePickup
 */
abstract class AbstractCollection extends DbAbstractCollection
{
    /**
     * @var StoreManager
     */
    protected StoreManager $storeManager;

    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;

    /**
     * AbstractCollection Constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param Manager $eventManager
     * @param StoreManager $storeManager
     * @param MetadataPool $metadataPool
     * @param Adapter|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactory   $entityFactory,
        Logger          $logger,
        FetchStrategy   $fetchStrategy,
        Manager         $eventManager,
        StoreManager    $storeManager,
        MetadataPool    $metadataPool,
        Adapter         $connection = null,
        AbstractDb      $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null): AbstractCollection
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        if ($field === 'holiday_id') {
            return $this->addHolidayFilter($condition);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
     * @param bool $withAdmin
     * @return $this
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    abstract public function addStoreFilter(Store|array|int $store, bool $withAdmin = true): AbstractCollection;

    /**
     * Add filter by holidays
     *
     * @param array|int|Holidays $holiday
     * @return $this
     */
    abstract public function addHolidayFilter(array|int|Holidays $holiday): AbstractCollection;

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql(): Select
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::GROUP);

        return $countSelect;
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     * @throws NoSuchEntityException
     */
    protected function performStoreAfterLoad(string $tableName, ?string $linkField): void
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['store_table' => $this->getTable($tableName)])
                ->where('store_table.' . $linkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);

            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = in_array(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    /*if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }*/

                    $stores = $this->storeManager->getStores(false, true);
                    $storeId = $storeIdKey ? current($stores)->getId() : current($storesData[$linkedId]);
                    $storeCode = $storeIdKey ? key($stores) : $this->storeManager->getStore($storeId)->getCode();

                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     * @throws NoSuchEntityException
     */
    protected function performHolidayAfterLoad(string $tableName, ?string $linkField): void
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['holidays_table' => $this->getTable($tableName)])
                ->where('holidays_table.' . $linkField . ' IN (?)', $linkedIds);
            $results = $connection->fetchAll($select);

            if ($results) {
                $holidayData = [];
                foreach ($results as $result) {
                    $holidayData[$result[$linkField]][] = $result['holiday_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($holidayData[$linkedId])) {
                        continue;
                    }
                    $item->setData('holiday_id', $holidayData[$linkedId]);
                }
            }
        }
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function performAddStoreFilter($store, bool $withAdmin = true): void
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Perform adding filter by holiday
     *
     * @param int|array|Holidays $holiday
     * @return void
     */
    protected function performAddHolidayFilter($holiday): void
    {
        if ($holiday instanceof Holidays) {
            $holiday = [$holiday->getId()];
        }

        if (!is_array($holiday)) {
            $holiday = [$holiday];
        }

        $this->addFilter('holiday', ['in' => $holiday], 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable(string $tableName, ?string $linkField): void
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinHolidayRelationTable(string $tableName, ?string $linkField): void
    {
        if ($this->getFilter('holiday')) {
            $this->getSelect()->join(
                ['holiday_table' => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = holiday_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }
}
