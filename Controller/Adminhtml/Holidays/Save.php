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
use MageINIC\StorePickup\Api\Data\HolidaysInterfaceFactory as HolidaysFactory;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface as DataPersistor;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Save Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::holiday_save';

    /**
     * @var HolidaysFactory
     */
    private HolidaysFactory $holidaysFactory;

    /**
     * @var HolidaysRepository
     */
    private HolidaysRepository $holidaysRepository;

    /**
     * @var DataPersistor|null
     */
    protected ?DataPersistor $dataPersistor = null;

    /**
     * Save Constructor.
     *
     * @param Context $context
     * @param HolidaysFactory $holidaysFactory
     * @param HolidaysRepository $holidaysRepository
     * @param DataPersistor $dataPersistor
     */
    public function __construct(
        Context            $context,
        HolidaysFactory    $holidaysFactory,
        HolidaysRepository $holidaysRepository,
        DataPersistor      $dataPersistor
    ) {
        parent::__construct($context);
        $this->holidaysFactory = $holidaysFactory;
        $this->holidaysRepository = $holidaysRepository;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            if (empty($data['holiday_id'])) {
                $data['holiday_id'] = null;
            }

            $model = $this->holidaysFactory->create();

            $id = $this->getRequest()->getParam('holiday_id');
            if ($id) {
                try {
                    $model = $this->holidaysRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This Holiday no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            $model->setData($data);
            try {
                $this->holidaysRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the Holiday.'));
                $this->dataPersistor->clear('mageinic_store_pickup');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['holiday_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong.'));
            }

            $this->dataPersistor->set('mageinic_store_pickup', $data);
            $holidayId = $this->getRequest()->getParam('holiday_id');
            return $resultRedirect->setPath('*/*/edit', ['holiday_id' => $holidayId]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
