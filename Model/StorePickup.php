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

namespace MageINIC\StorePickup\Model;

use Exception;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Helper\Data;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup as ResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class for StorePickup
 *
 * @api
 * @method StorePickup setStoreId(int $storeId)
 * @method int getStoreId()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class StorePickup extends AbstractModel implements StorePickupInterface, IdentityInterface
{
    /**
     * MageINIC page cache tag
     */
    public const CACHE_TAG = 'mageinic_store_pickup';

    /**
     * @var string
     */
    protected $cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'store_pickup';

    /**
     * @var string
     */
    protected $_eventObject = 'store_pickup';

    /**
     * @var string
     */
    protected $_idFieldName = StorePickupInterface::STORE_PICKUP_ID;

    /**
     * @var StoreManager
     */
    private StoreManager $storeManager;

    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * StorePickup Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManager $storeManager
     * @param Data $helperData
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context          $context,
        Registry         $registry,
        StoreManager     $storeManager,
        Data             $helperData,
        AbstractResource $resource = null,
        AbstractDb       $resourceCollection = null,
        array            $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Receive Stores.
     *
     * @return array
     */
    public function getStores(): array
    {
        return (array)$this->getData(self::STORE);
    }

    /**
     * @inheritdoc
     */
    public function getHolidays(): ?array
    {
        return $this->getData(self::STORE_HOLIDAYS);
    }

    /**
     * @inheritDoc
     */
    public function setHolidays(array $holidays): StorePickupInterface
    {
        return $this->setData(self::STORE_HOLIDAYS, $holidays);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getEntityId(): ?int
    {
        return $this->getData(self::STORE_PICKUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($id): StorePickupInterface
    {
        return $this->setData(self::STORE_PICKUP_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::STORE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): StorePickupInterface
    {
        return $this->setData(self::STORE_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getAddress(): ?string
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setAddress($address): StorePickupInterface
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @inheritdoc
     */
    public function getContent(): ?string
    {
        return $this->getData(self::STORE_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setContent(string $content): StorePickupInterface
    {
        return $this->setData(self::STORE_CONTENT, $content);
    }

    /**
     * @inheritdoc
     */
    public function getCity(): ?string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity(string $city): StorePickupInterface
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode(): ?string
    {
        return $this->getData(self::ZIPCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode(string $postcode): StorePickupInterface
    {
        return $this->setData(self::ZIPCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function getStateId(): ?string
    {
        return $this->getData(self::STATE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStateId(string $state): StorePickupInterface
    {
        return $this->setData(self::STATE_ID, $state);
    }

    /**
     * Receive Store Region
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->getData(self::REGION);
    }

    /**
     * Set Store Region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region): StorePickupInterface
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function getCountry(): ?string
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @inheritdoc
     */
    public function setCountry(string $country): StorePickupInterface
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * @inheritdoc
     */
    public function getContactNo(): ?string
    {
        return $this->getData(self::CONTACT_NO);
    }

    /**
     * @inheritdoc
     */
    public function setContactNo(string $contactNo): StorePickupInterface
    {
        return $this->setData(self::CONTACT_NO, $contactNo);
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail(string $email): StorePickupInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): ?bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive): StorePickupInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt): StorePickupInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $updatedAt): StorePickupInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritdoc
     */
    public function getLongitude(): ?string
    {
        return $this->getData(self::LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude(string $longitude): StorePickupInterface
    {
        return $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @inheritdoc
     */
    public function getLatitude(): ?string
    {
        return $this->getData(self::LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude(string $latitude): StorePickupInterface
    {
        return $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * @inheritdoc
     */
    public function getImage(): ?string
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage(string $image): StorePickupInterface
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getSchedule(): ?string
    {
        return $this->getData(self::STORE_SCHEDULE);
    }

    /**
     * @inheritdoc
     */
    public function setSchedule(string $schedule): StorePickupInterface
    {
        return $this->setData(self::STORE_SCHEDULE, $schedule);
    }

    /**
     * @inheritdoc
     */
    public function getWebsite(): ?string
    {
        return $this->getData(self::STORE_WEBSITE);
    }

    /**
     * @inheritdoc
     */
    public function setWebsite(string $url): StorePickupInterface
    {
        return $this->setData(self::STORE_WEBSITE, $url);
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): ?int
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(int $position): StorePickupInterface
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        $urlPrefix = $this->helperData->getRoute() ? $this->helperData->getRoute() . '/' : '';
        return $this->storeManager->getStore()->getBaseUrl()
            . $urlPrefix . $this->getIdentifier() . $this->helperData->getUrlSuffix();
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ?string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * @inheritdoc
     */
    public function setIdentifier(string $identifier): StorePickupInterface
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle(string $metaTitle): StorePickupInterface
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeywords(): ?string
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeywords(string $metaKeywords): StorePickupInterface
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription(string $metaDescription): StorePickupInterface
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }
}
