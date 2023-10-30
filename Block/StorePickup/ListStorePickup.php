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

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\Collection;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\CollectionFactory;
use MageINIC\StorePickup\Model\StorePickup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Breadcrumbs;
use Magento\Theme\Block\Html\Pager as HtmlPager;
use Magento\Theme\Block\Html\Title;

/**
 * Class for ListStorePickup
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListStorePickup extends Template implements IdentityInterface
{
    public const LIST_PAGE_META_TITLE = 'store_pickup/store_list_view/meta_title';
    public const LIST_PAGE_META_KEYWORDS = 'store_pickup/store_list_view/meta_keywords';
    public const LIST_PAGE_META_DESCRIPTION = 'store_pickup/store_list_view/meta_description';

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * ListStorePickup Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context           $context,
        CollectionFactory $collectionFactory,
        array             $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Receive Pager Html
     *
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     * @throws LocalizedException
     */
    public function getIdentities(): array
    {
        $identities = [];
        if ($this->getCollection()) {
            foreach ($this->getCollection() as $storePickup) {
                if ($storePickup instanceof IdentityInterface) {
                    $identities[] = $storePickup->getIdentities();
                }
            }
        }
        $identities = array_merge([], ...$identities);

        return $identities ?: [StorePickup::CACHE_TAG];
    }

    /**
     * Receive Store Pickup Collection
     *
     * @return Collection
     * @throws LocalizedException
     */
    public function getCollection(): Collection
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 3;
        $pickupCollection = $this->collectionFactory->create();
        $pickupCollection->addFieldToFilter(StorePickupInterface::IS_ACTIVE, true);
        $pickupCollection->addStoreFilter($this->_storeManager->getStore()->getId());
        $pickupCollection->setOrder(StorePickupInterface::POSITION, SortOrder::SORT_ASC);
        $pickupCollection->setPageSize($pageSize);
        $pickupCollection->setCurPage($page);

        return $pickupCollection;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout(): ListStorePickup
    {
        $this->_addBreadcrumbs();
        $metaTitle = $this->getMetaTitle();
        $this->pageConfig->getTitle()->set($metaTitle ?: '');
        $this->pageConfig->setKeywords($this->getMetaKeywords());
        $this->pageConfig->setDescription($this->getMetaDescription());
        parent::_prepareLayout();

        $page_size = $this->getPagerCount();
        $page_data = $this->getCollection();

        if ($this->getCollection()) {
            /** @var Title $pageMainTitle */
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            $pageMainTitle?->setPageTitle($metaTitle);

            $pager = $this->getLayout()
                ->createBlock(HtmlPager::class, 'custom.pager.name')
                ->setAvailableLimit($page_size)
                ->setShowPerPage(false)
                ->setCollection($page_data);

            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * Receive Pager Count
     *
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getPagerCount(): array
    {
        $minimum_show = 3;
        $page_array = [];
        $list_data = $this->collectionFactory->create();
        $list_data->addFieldToFilter(StorePickupInterface::IS_ACTIVE, true);
        $list_data->addStoreFilter($this->_storeManager->getStore()->getId());
        $list_count = ceil(count($list_data->getData()));
        $show_count = $minimum_show + 1;

        if (count($list_data->getData()) >= $show_count) {
            $list_count = $list_count / $minimum_show;
            $page_nu = $total = $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
            for ($x = 0; $x <= $list_count; $x++) {
                $total = $total + $page_nu;
                $page_array[$total] = $total;
            }
        } else {
            $page_array[$minimum_show] = $minimum_show;
            $minimum_show = $minimum_show + $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
        }

        return $page_array;
    }

    /**
     * Prepare breadcrumbs
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _addBreadcrumbs(): void
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
        return $this->_scopeConfig->getValue(self::LIST_PAGE_META_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Receive Meta Keywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): ?string
    {
        return $this->_scopeConfig->getValue(self::LIST_PAGE_META_KEYWORDS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Receive Meta Description
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->_scopeConfig->getValue(self::LIST_PAGE_META_DESCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return ListStorePickup
     * @throws LocalizedException
     */
    protected function _beforeToHtml(): ListStorePickup
    {
        $this->setCollection($this->getCollection());
        return parent::_beforeToHtml();
    }
}
