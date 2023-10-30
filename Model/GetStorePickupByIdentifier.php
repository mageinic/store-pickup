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

namespace MageINIC\StorePickup\Model;

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterfaceFactory as StorePickupFactory;
use MageINIC\StorePickup\Api\GetStorePickupByIdentifierInterface;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetPageByIdentifier
 */
class GetStorePickupByIdentifier implements GetStorePickupByIdentifierInterface
{
    /**
     * @var StorePickupFactory
     */
    private StorePickupFactory $storePickupFactory;

    /**
     * @var StorePickup
     */
    private StorePickup $resource;

    /**
     * GetStorePickupByIdentifier Constructor.
     *
     * @param StorePickupFactory $storePickupFactory
     * @param StorePickup $resource
     */
    public function __construct(
        StorePickupFactory $storePickupFactory,
        StorePickup        $resource
    ) {
        $this->storePickupFactory = $storePickupFactory;
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $identifier, int $storeId) : StorePickupInterface
    {
        $storePickup = $this->storePickupFactory->create();
        $storePickup->setStoreId($storeId);
        $this->resource->load($storePickup, $identifier, StorePickupInterface::IDENTIFIER);

        if (!$storePickup->getId()) {
            throw new NoSuchEntityException(__('The Store Pickup with the "%1" ID doesn\'t exist.', $identifier));
        }

        return $storePickup;
    }
}
