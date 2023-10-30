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

namespace MageINIC\StorePickup\Block\StorePickup;

use MageINIC\StorePickup\Api\Data\HolidaysInterface;
use MageINIC\StorePickup\Api\Data\HolidaysSearchResultsInterface;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Api\GetStorePickupByIdentifierInterface as PickupByIdentifier;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use MageINIC\StorePickup\Helper\Data;
use MageINIC\StorePickup\Model\StorePickup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Breadcrumbs;
use Magento\Theme\Block\Html\Title;
use Zend_Db_Expr;

/**
 * Class for Store Pickup View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends Template implements IdentityInterface
{
    /**
     * @var PickupByIdentifier
     */
    protected PickupByIdentifier $pickupByIdentifier;

    /**
     * @var HolidaysRepository
     */
    protected HolidaysRepository $holidaysRepository;

    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * View Constructor.
     *
     * @param Context $context
     * @param PickupByIdentifier $pickupByIdentifier
     * @param HolidaysRepository $holidaysRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context               $context,
        PickupByIdentifier    $pickupByIdentifier,
        HolidaysRepository    $holidaysRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data                  $helperData,
        array                 $data = []
    ) {
        $this->pickupByIdentifier = $pickupByIdentifier;
        $this->holidaysRepository = $holidaysRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Receive Store Pickup full address.
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAddress(): string
    {
        $storePickup = $this->getStorePickup();
        return implode(', ', [
            $storePickup->getAddress(),
            $storePickup->getCity(),
            $storePickup->getRegion(),
            $storePickup->getPostcode(),
            $this->helperData->getCountryByCode($storePickup->getCountry())
        ]);
    }

    /**
     * Receive Store Pickup Data.
     *
     * @return StorePickupInterface
     * @throws NoSuchEntityException
     */
    public function getStorePickup(): StorePickupInterface
    {
        $identifier = $this->getRequest()->getParam('identifier_key');
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->pickupByIdentifier->execute($identifier, $storeId);
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getIdentities(): array
    {
        return [StorePickup::CACHE_TAG . '_' . $this->getStorePickup()->getEntityId()];
    }

    /**
     * Receive Holidays Collection
     *
     * @return HolidaysSearchResultsInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getHolidays()
    {
        $date = $this->_localeDate->date();
        $todayStartOfDayDate = $date->setTime(0, 0, 0)->format('Y-m-d H:i:s');

        $holidaysId = $this->getStorePickup()->getHolidays();
        $searchCriteria = $this->searchCriteriaBuilder
           ->addFilter(
               'to_date',
               [
                    'or' => [
                        0 => ['date' => true, 'from' => $todayStartOfDayDate],
                        1 => ['is' => new Zend_Db_Expr('null')],
                    ]
                ],
               'left'
           )->addFilter(HolidaysInterface::HOLIDAY_ID, $holidaysId, 'in')
            ->addFilter(HolidaysInterface::IS_ACTIVE, true)
            ->create();

        return $this->holidaysRepository->getList($searchCriteria);
    }

    /**
     * Prepare global layout
     *
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function _prepareLayout(): View
    {
        $storePickup = $this->getStorePickup();
        $this->_addBreadcrumbs($storePickup);
        $this->pageConfig->addBodyClass('mageinic-store-pickup-' . $storePickup->getIdentifier());
        $metaTitle = $storePickup->getMetaTitle();
        $this->pageConfig->getTitle()->set($metaTitle ?: $storePickup->getName());
        $this->pageConfig->setKeywords($storePickup->getMetaKeywords());
        $this->pageConfig->setDescription($storePickup->getMetaDescription());

        /** @var Title $pageMainTitle */
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        $pageMainTitle?->setPageTitle($storePickup->getName());

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param StorePickupInterface $storePickup
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.IfStatementAssignment)
     */
    protected function _addBreadcrumbs(StorePickupInterface $storePickup): void
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbsBlock) {
            /** @var Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'store-pickup_listing_page',
                [
                    'label' => $this->getMetaTitle(),
                    'title' => __('Go to Store pickup List Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . $this->helperData->getRoute()
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'store_pickup_detail_page',
                [
                    'label' => $storePickup->getName(),
                    'title' => $storePickup->getName()
                ]
            );
        }
    }

    /**
     * Receive Meta Title
     *
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->_scopeConfig->getValue(ListStorePickup::LIST_PAGE_META_TITLE, ScopeInterface::SCOPE_STORE);
    }
}
