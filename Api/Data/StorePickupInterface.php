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

namespace MageINIC\StorePickup\Api\Data;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface StorePickup Interface
 *
 * @api
 */
interface StorePickupInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const STORE_PICKUP_ID = 'entity_id';
    public const IS_ACTIVE = 'is_active';
    public const IMAGE = 'image';
    public const STORE_NAME = 'name';
    public const EMAIL = 'email';
    public const CONTACT_NO = 'contact_no';
    public const ADDRESS = 'address';
    public const STORE_CONTENT = 'content';
    public const CITY = 'city';
    public const REGION = 'region';
    public const ZIPCODE = 'postcode';
    public const LATITUDE = 'latitude';
    public const LONGITUDE = 'longitude';
    public const STATE_ID = 'state_id';
    public const COUNTRY = 'country_id';
    public const UPDATED_AT = 'updated_at';
    public const CREATED_AT = 'created_at';
    public const STORE_HOLIDAYS = 'holiday_id';
    public const STORE = 'store_id';
    public const STORE_SCHEDULE = 'schedule';
    public const STORE_WEBSITE = 'website';
    public const POSITION = 'position';
    public const IDENTIFIER = 'identifier';
    public const META_TITLE = 'meta_title';
    public const META_KEYWORDS = 'meta_keywords';
    public const META_DESCRIPTION = 'meta_description';
    /**#@-*/

    /**
     * Receive Store ID
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set Store ID
     *
     * @param int $id
     * @return StorePickupInterface
     */
    public function setEntityId(int $id): StorePickupInterface;

    /**
     * Receive Store name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set Store name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): StorePickupInterface;

    /**
     * Receive Store address
     *
     * @return string|null
     */
    public function getAddress(): ?string;

    /**
     * Set Store address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address): StorePickupInterface;

    /**
     * Receive content
     *
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): StorePickupInterface;

    /**
     * Receive city
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): StorePickupInterface;

    /**
     * Receive postcode
     *
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode(string $postcode): StorePickupInterface;

    /**
     * Receive state
     *
     * @return string|null
     */
    public function getStateId(): ?string;

    /**
     * Set state
     *
     * @param string $state
     * @return $this
     */
    public function setStateId(string $state): StorePickupInterface;

    /**
     * Receive Store Region
     *
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * Set Store Region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region): StorePickupInterface;

    /**
     * Receive country
     *
     * @return string|null
     */
    public function getCountry(): ?string;

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country): StorePickupInterface;

    /**
     * Receive Store contact no
     *
     * @return string|null
     */
    public function getContactNo(): ?string;

    /**
     * Set Store contact no
     *
     * @param string $contactNo
     * @return $this
     */
    public function setContactNo(string $contactNo): StorePickupInterface;

    /**
     * Receive Store email
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Set Store email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): StorePickupInterface;

    /**
     * Receive Store is Active
     *
     * @return bool|null
     */
    public function getIsActive(): ?bool;

    /**
     * Set Store is Active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): StorePickupInterface;

    /**
     * Receive Store create at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set create at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): StorePickupInterface;

    /**
     * Receive Store updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set Store updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): StorePickupInterface;

    /**
     * Receive Store longitude
     *
     * @return string|null
     */
    public function getLongitude(): ?string;

    /**
     * Set Store longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude(string $longitude): StorePickupInterface;

    /**
     * Receive Store latitude
     *
     * @return string|null
     */
    public function getLatitude(): ?string;

    /**
     * Set Store latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude(string $latitude): StorePickupInterface;

    /**
     * Receive Store image
     *
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * Set Store image
     *
     * @param string $image
     * @return $this
     */
    public function setImage(string $image): StorePickupInterface;

    /**
     * Receive Store Pickup Url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getUrl(): string;

    /**
     * Get holidays
     *
     * @return string[]|null
     */
    public function getHolidays(): ?array;

    /**
     * Set holidays
     *
     * @param string[] $holidays
     * @return $this
     */
    public function setHolidays(array $holidays): StorePickupInterface;

    /**
     * Receive Store schedule
     *
     * @return string|null
     */
    public function getSchedule(): ?string;

    /**
     * Set Store schedule
     *
     * @param string $schedule
     * @return $this
     */
    public function setSchedule(string $schedule): StorePickupInterface;

    /**
     * Receive Store url
     *
     * @return string|null
     */
    public function getWebsite(): ?string;

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setWebsite(string $url): StorePickupInterface;

    /**
     * Receive Store url
     *
     * @return int|null
     */
    public function getPosition(): ?int;

    /**
     * Set url
     *
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position): StorePickupInterface;

    /**
     * Receive Store Identifier
     *
     * @return string|null
     */
    public function getIdentifier(): ?string;

    /**
     * Set Store Identifier
     *
     * @param string $identifier
     * @return StorePickupInterface
     */
    public function setIdentifier(string $identifier): StorePickupInterface;

    /**
     * Receive Meta Title
     *
     * @return string|null
     */
    public function getMetaTitle(): ?string;

    /**
     * Set Meta Title
     *
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle(string $metaTitle): StorePickupInterface;

    /**
     * Receive MetaKeywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): ?string;

    /**
     * Set meta_keywords
     *
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords(string $metaKeywords): StorePickupInterface;

    /**
     * Receive MetaDescription
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string;

    /**
     * Set meta_description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription(string $metaDescription): StorePickupInterface;
}
