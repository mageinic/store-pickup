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

use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use MageINIC\StorePickup\Controller\Adminhtml\StorePickup;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Edit Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends StorePickup implements HttpGetActionInterface
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
     * @return Page|Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->storePickupFactory->create();

        if ($id) {
            $model = $this->storePickupRepository->getById($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This Store Pickup no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('mageinic_store_pickup', $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit StorePickup') : __('New StorePickup'),
            $id ? __('Edit StorePickup') : __('New StorePickup')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('StorePickups'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Store Pickup: "%1"', $model->getName()) : __('New Store Pickup')
        );
        return $resultPage;
    }
}
