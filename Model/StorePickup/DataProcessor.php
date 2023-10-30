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

declare(strict_types=1);

namespace MageINIC\StorePickup\Model\StorePickup;

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterfaceFactory as StorePickupFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * StorePickup Data Processor convert StorePickup data Array in to object
 */
class DataProcessor
{
    /**
     * @var StorePickupFactory
     */
    private StorePickupFactory $storePickupFactory;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected DataObjectProcessor $dataObjectProcessor;

    /**
     * DataProcessor Constructor.
     *
     * @param StorePickupFactory $storePickupFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        StorePickupFactory  $storePickupFactory,
        DataObjectHelper    $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->storePickupFactory = $storePickupFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * StorePickup data array convert into object
     *
     * @param array $data
     * @param StorePickupInterface|null $storePickup
     * @return StorePickupInterface
     */
    public function dataObjectProcessor(array $data, StorePickupInterface $storePickup = null): StorePickupInterface
    {
        $storePickup = $storePickup ?? $this->storePickupFactory->create();
        $requiredDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $storePickup,
            StorePickupInterface::class
        );
        $storePickupData = array_merge($requiredDataAttributes, $data);
        $this->dataObjectHelper->populateWithArray(
            $storePickup,
            $storePickupData,
            StorePickupInterface::class
        );

        return $storePickup;
    }
}
