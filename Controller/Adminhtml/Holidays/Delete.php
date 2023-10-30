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
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class for Delete Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::holiday_delete';

    /**
     * @var HolidaysRepository
     */
    protected HolidaysRepository $holidaysRepository;

    /**
     * Delete Constructor.
     *
     * @param Context $context
     * @param HolidaysRepository $holidaysRepository
     */
    public function __construct(
        Context            $context,
        HolidaysRepository $holidaysRepository
    ) {
        $this->holidaysRepository = $holidaysRepository;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('holiday_id');

        if ($id) {
            try {
                $this->holidaysRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the Store Holiday.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['holiday_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Store Holiday to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
