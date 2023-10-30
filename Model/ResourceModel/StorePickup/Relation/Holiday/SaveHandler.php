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

namespace MageINIC\StorePickup\Model\ResourceModel\StorePickup\Relation\Holiday;

use Exception;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup;

/**
 * Class for SaveHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;

    /**
     * @var StorePickup
     */
    protected StorePickup $resource;

    /**
     * SaveHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param StorePickup $resource
     */
    public function __construct(
        MetadataPool $metadataPool,
        StorePickup  $resource
    ) {
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
    }

    /**
     * Perform action on relation/extension attribute.
     *
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = []): object
    {
        /** @var StorePickupInterface $entity */
        $entityMetadata = $this->metadataPool->getMetadata(StorePickupInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $this->resource->getConnection();
        $newHolidays = (array)$entity->getHolidayId();
        $oldHolidays = $this->resource->lookupHolidayIds((int)$entity->getId());
        $table = $this->resource->getTable('mageinic_store_pickup_holidays');
        $insert = array_diff($newHolidays, $oldHolidays);
        $delete = array_diff($oldHolidays, $newHolidays);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'holiday_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $holidayId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'holiday_id' => (int)$holidayId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
