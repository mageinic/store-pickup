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

namespace MageINIC\StorePickup\Observer;

use MageINIC\StorePickup\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use MageINIC\StorePickup\Api\StorePickupRepositoryInterface as StorePickupRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class for SaveInQuote
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveInQuote implements ObserverInterface
{
    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Execute Method.
     *
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException|LocalizedException
     */
    public function execute(Observer $observer): SaveInQuote
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $shippingMethod = $order->getShippingMethod();

        $extension = $order->getExtensionAttributes();
        $extension->getStorePickupId($quote->getData('store_pickup_id'));
        $extension->setStorePickupDate($quote->getData('store_pickup_date'));

        $order->setData('store_pickup_id', $quote->getData('store_pickup_id'));
        $order->setData('store_pickup_date', $quote->getData('store_pickup_date'));

        $storeId = $quote->getData('store_pickup_id');

        if ($storeId != '') {
            if ($shippingMethod == "mageinic_store_pickup_mageinic_store_pickup") {
                $storePickupData = $this->helperData->getPickupStoreAddress($storeId);
                $order->getShippingAddress()->addData($storePickupData);
            }
        }

        return $this;
    }
}
