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

namespace MageINIC\StorePickup\Controller\Adminhtml;

use MageINIC\StorePickup\Helper\Data;
use MageINIC\StorePickup\Model\ImageUploader;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Request\DataPersistorInterface as DataPersistor;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use MageINIC\StorePickup\Api\Data\StorePickupInterfaceFactory as StorePickUpFactory;
use MageINIC\StorePickup\Api\StorePickupRepositoryInterface as StorePickupRepository;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class for StorePickup Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class StorePickup extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::manage_store_pickup';

    /**
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var StorePickUpFactory
     */
    protected StorePickUpFactory $storePickupFactory;

    /**
     * @var StorePickupRepository
     */
    protected StorePickupRepository $storePickupRepository;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var DataPersistor|null
     */
    protected ?DataPersistor $dataPersistor = null;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected ForwardFactory $resultForwardFactory;

    /**
     * @var ImageUploader
     */
    protected ImageUploader $imageUploader;

    /**
     * @var Json
     */
    protected Json $serialize;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * StorePickup Constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param JsonFactory $jsonFactory
     * @param Filter $filter
     * @param StorePickUpFactory $storePickupFactory
     * @param StorePickupRepository $storePickupRepository
     * @param CollectionFactory $collectionFactory
     * @param DataPersistor $dataPersistor
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param ImageUploader $imageUploader
     * @param Json $serialize
     * @param Data $helperData
     * @param Logger $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context               $context,
        Registry              $coreRegistry,
        JsonFactory           $jsonFactory,
        Filter                $filter,
        StorePickUpFactory    $storePickupFactory,
        StorePickupRepository $storePickupRepository,
        CollectionFactory     $collectionFactory,
        DataPersistor         $dataPersistor,
        PageFactory           $resultPageFactory,
        ForwardFactory        $resultForwardFactory,
        ImageUploader         $imageUploader,
        Json                  $serialize,
        Data                  $helperData,
        Logger                $logger
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->jsonFactory = $jsonFactory;
        $this->filter = $filter;
        $this->storePickupFactory = $storePickupFactory;
        $this->storePickupRepository = $storePickupRepository;
        $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->imageUploader = $imageUploader;
        $this->serialize = $serialize;
        $this->logger = $logger;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param Page $resultPage
     * @return Page
     */
    public function initPage(Page $resultPage): Page
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('MageINIC'), __('MageINIC'))
            ->addBreadcrumb(__('StorePickup'), __('StorePickup'));
        return $resultPage;
    }
}
