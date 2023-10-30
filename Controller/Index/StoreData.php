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

namespace MageINIC\StorePickup\Controller\Index;

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Model\StorePickupRepository;
use MageINIC\StorePickup\ViewModel\StorePickup;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class for Store Data Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreData extends Action implements HttpPostActionInterface
{
    /**
     * @var StorePickup
     */
    private StorePickup $storePickup;

    /**
     * @var StorePickupRepository
     */
    protected StorePickupRepository $StorePickupRepository;

    /**
     * StoreData Constructor.
     *
     * @param Context $context
     * @param StorePickup $storePickup
     * @param StorePickupRepository $StorePickupRepository
     */
    public function __construct(
        Context               $context,
        StorePickup           $storePickup,
        StorePickupRepository $StorePickupRepository
    ) {
        $this->storePickup = $storePickup;
        $this->StorePickupRepository = $StorePickupRepository;
        parent::__construct($context);
    }

    /**
     * Perform Execute Method
     *
     * @return Json|(Json&ResultInterface)|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $storeArray = [
            'status' => 'failure',
        ];

        if ($this->getRequest()->isAjax()) {
            if ($this->getPickupStores()) {
                $storeData = $this->getPickupStores();
                $storeArray = [
                    'status' => 'success',
                    'entity_id' => $storeData->getEntityId(),
                    'content' => $this->storePickup->getStoreInfo($storeData),
                    'name' => $storeData->getName(),
                    'address' => $storeData->getAddress(),
                    'city' => $storeData->getCity(),
                    'country' => $this->storePickup->getCountryByCode($storeData->getCountry()),
                    'country_id' => $storeData->getCountry(),
                    'state' => $storeData->getRegion(),
                    'state_id' => $storeData->getStateId(),
                    'state_code' => $storeData->getRegion(),
                    'region' => $storeData->getRegion(),
                    'postcode' => $storeData->getPostcode(),
                    'contact_no' => $storeData->getContactNo(),
                ];
            }

            $resultPage->setData($storeArray);
        }

        return $resultPage;
    }

    /**
     * Receive Store Pickup data By id
     *
     * @return StorePickupInterface $pickupCollection
     * @throws LocalizedException
     */
    public function getPickupStores(): StorePickupInterface
    {
        $id = $this->getRequest()->getParam('entity_id');
        return $this->StorePickupRepository->getById($id);
    }
}
