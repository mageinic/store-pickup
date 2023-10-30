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

namespace MageINIC\StorePickup\Model\ResourceModel\StorePickup;

use Exception;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Model\Holidays;
use MageINIC\StorePickup\Model\StorePickup as Model;
use MageINIC\StorePickup\Model\ResourceModel\AbstractCollection;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup as ResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * StorePickup Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = StorePickupInterface::STORE_PICKUP_ID;

    /**
     * @var string
     */
    protected $eventPrefix = 'mageinic_brand_slider_collection';

    /**
     * @var string
     */
    protected $eventObject = 'brand_slider_collection';

    /**
     * Returns pairs entity_id - brand_name
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->_toOptionArray(
            StorePickupInterface::STORE_PICKUP_ID,
            StorePickupInterface::STORE_NAME
        );
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
     * @param bool $withAdmin
     * @return $this
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function addStoreFilter(Store|array|int $store, bool $withAdmin = true): Collection
    {
        $this->performAddStoreFilter($store, $withAdmin);

        return $this;
    }

    /**
     * Add filter by store
     *
     * @param array|int|Holidays $holiday
     * @return $this
     */
    public function addHolidayFilter(array|int|Holidays $holiday): Collection
    {
        $this->performAddHolidayFilter($holiday);
        return $this;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['holiday'] = 'holiday_table.holiday_id';
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function _afterLoad(): Collection
    {
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $this->performStoreAfterLoad('mageinic_store_pickup_store', $entityMetadata->getLinkField());
        $this->performHolidayAfterLoad('mageinic_store_pickup_holidays', $entityMetadata->getLinkField());

        return parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     * @throws Exception
     */
    protected function _renderFiltersBefore(): void
    {
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $this->joinStoreRelationTable('mageinic_store_pickup_store', $entityMetadata->getLinkField());
        $this->joinHolidayRelationTable('mageinic_store_pickup_holidays', $entityMetadata->getLinkField());
    }
}
