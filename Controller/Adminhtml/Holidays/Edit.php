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

use MageINIC\StorePickup\Api\Data\HolidaysInterfaceFactory as HolidaysFactory;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class for Edit Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::holiday_save';

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @var HolidaysFactory
     */
    private HolidaysFactory $holidaysFactory;

    /**
     * @var HolidaysRepository
     */
    private HolidaysRepository $holidaysRepository;

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * Edit Constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param HolidaysFactory $holidaysFactory
     * @param HolidaysRepository $holidaysRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context            $context,
        Registry           $coreRegistry,
        HolidaysFactory    $holidaysFactory,
        HolidaysRepository $holidaysRepository,
        PageFactory        $resultPageFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->holidaysFactory = $holidaysFactory;
        $this->holidaysRepository = $holidaysRepository;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageINIC_StorePickup::holiday')
            ->addBreadcrumb(__('MageINIC'), __('MageINIC'))
            ->addBreadcrumb(__('Manage Holidays'), __('Manage Holidays'));
        return $resultPage;
    }

    /**
     * Execute action based on request and return result
     *
     * @return Page|Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('holiday_id');
        $model = $this->holidaysFactory->create();

        if ($id) {
            $model = $this->holidaysRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Store Holiday no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('mageinic_store_pickup', $model);

        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Store Holiday') : __('New Store Holiday'),
            $id ? __('Edit Store Holiday') : __('New Store Holiday')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Store Holidays'));
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Holiday: "%1"', $model->getName()) : __('New Store Holidays')
        );

        return $resultPage;
    }
}
