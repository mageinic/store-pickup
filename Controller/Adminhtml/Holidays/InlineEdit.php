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

namespace MageINIC\StorePickup\Controller\Adminhtml\Holidays;

use Exception;
use MageINIC\StorePickup\Api\Data\HolidaysInterface;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;

/**
 * Class for InlineEdit Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::holiday_save';

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var HolidaysRepository
     */
    private HolidaysRepository $holidaysRepository;

    /**
     * InlineEdit Constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param HolidaysRepository $holidaysRepository
     */
    public function __construct(
        Context            $context,
        JsonFactory        $jsonFactory,
        HolidaysRepository $holidaysRepository
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->holidaysRepository = $holidaysRepository;
        parent::__construct($context);
    }

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
            $storeHoliday = $this->dataLoad($id);
            try {
                $StorePickupData = $postItems[$id];
                $storeHoliday->addData($StorePickupData);
                $this->dataSave($storeHoliday);
            } catch (LocalizedException|RuntimeException $e) {
                $messages[] = $this->getErrorWithHolidayId($storeHoliday, $e->getMessage());
                $error = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithHolidayId(
                    $storeHoliday,
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
     * Add Store Holidays id to error message
     *
     * @param HolidaysInterface $storeHoliday
     * @param string $errorText
     * @return string
     */
    private function getErrorWithHolidayId(HolidaysInterface $storeHoliday, string $errorText): string
    {
        return '[Holiday ID: ' . $storeHoliday->getId() . '] ' . $errorText;
    }

    /**
     * Save Data
     *
     * @param HolidaysInterface $storeHoliday
     * @return void
     * @throws LocalizedException
     */
    private function dataSave(HolidaysInterface $storeHoliday): void
    {
        $this->holidaysRepository->save($storeHoliday);
    }

    /**
     * Load Data by ID
     *
     * @param int $id
     * @return HolidaysInterface
     * @throws LocalizedException
     */
    private function dataLoad(int $id): HolidaysInterface
    {
        return $this->holidaysRepository->getById($id);
    }
}
