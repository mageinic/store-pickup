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
#
namespace MageINIC\StorePickup\Model;

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory;

/**
 * Class for PickupStoreConfigProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigProvider implements ConfigProviderInterface
{
    public const ENABLE = 'store_pickup/general/enable';

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var ScopeConfig
     */
    protected ScopeConfig $scopeConfiguration;

    /**
     * @var StoreManager
     */
    private StoreManager $storeManager;

    /**
     * ConfigProvider Constructor
     *
     * @param ScopeConfig $scopeConfiguration
     * @param CollectionFactory $collectionFactory
     * @param StoreManager $storeManager
     */
    public function __construct(
        ScopeConfig       $scopeConfiguration,
        CollectionFactory $collectionFactory,
        StoreManager      $storeManager
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Receive Store Pickup collection
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStorePickup(): array
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToSelect([StorePickupInterface::STORE_PICKUP_ID, StorePickupInterface::STORE_NAME])
            ->addFieldToFilter(StorePickupInterface::IS_ACTIVE, true)
            ->setOrder(StorePickupInterface::STORE_NAME, SortOrder::SORT_ASC)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->getData();

        return array_column($collection, 'name', 'entity_id');
    }

    /**
     * Receive Config value
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        $pickupCollection = $this->getStorePickup();

        $storeList = [];
        $storeList['pickup_stores'] = $pickupCollection;
        $enabled = $this->scopeConfiguration->getValue(self::ENABLE, ScopeInterface::SCOPE_STORE);
        $storeList['show_hide_store_pickup_block'] = (bool)$enabled;

        return $storeList;
    }
}
