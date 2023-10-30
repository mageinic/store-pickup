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
 * @package MageINIC_<ModuleName>
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\StorePickup\Controller\Adminhtml\StorePickup;

use Exception;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Controller\Adminhtml\StorePickup;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;

/**
 * Class for InlineEdit Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends StorePickup implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::save';

    /**
     * Execute action based on request and return result
     *
     * @return Json
     * @throws LocalizedException
     */
    public function execute(): Json
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $id) {
            $storePickup = $this->dataLoad($id);
            try {
                $StorePickupData = $postItems[$id];
                $storePickup->addData($StorePickupData);
                $this->dataSave($storePickup);
            } catch (LocalizedException|RuntimeException $e) {
                $messages[] = $this->getErrorWithStorePickupId($storePickup, $e->getMessage());
                $error = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithStorePickupId(
                    $storePickup,
                    __('Something went wrong while saving the page.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Store Pickup id to error message
     *
     * @param StorePickUpInterface $storePickup
     * @param string $errorText
     * @return string
     */
    private function getErrorWithStorePickupId(StorePickUpInterface $storePickup, string $errorText): string
    {
        return '[Store Pick-up ID: ' . $storePickup->getEntityId() . '] ' . $errorText;
    }

    /**
     * Save Data
     *
     * @param StorePickupInterface $storePickup
     * @return void
     * @throws LocalizedException
     */
    private function dataSave(StorePickupInterface $storePickup): void
    {
        $this->storePickupRepository->save($storePickup);
    }

    /**
     * Load Data by ID
     *
     * @param int $id
     * @return StorePickUpInterface
     * @throws LocalizedException
     */
    private function dataLoad(int $id): StorePickUpInterface
    {
        return $this->storePickupRepository->getById($id);
    }
}
