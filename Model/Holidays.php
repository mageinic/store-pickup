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

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use MageINIC\StorePickup\Api\Data\HolidaysInterface;

/**
 * Class for Store Holidays
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Holidays extends AbstractModel implements HolidaysInterface, IdentityInterface
{
    /**#@+
     * Holiday's statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * @var string
     */
    protected $_eventPrefix = 'mageinic_store_holidays';

    /**
     * MageINIC page cache tag
     */
    public const CACHE_TAG = 'mageinic_store_holidays';

    /**
     * @var string
     */
    protected $cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventObject = 'store_holidays';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = HolidaysInterface::HOLIDAY_ID;

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->getData(self::HOLIDAY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id): HolidaysInterface
    {
        return $this->setData(self::HOLIDAY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): HolidaysInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(bool $status): HolidaysInterface
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }

    /**
     * @inheritdoc
     */
    public function getFromDate(): ?string
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setFromDate(string $fromDate): HolidaysInterface
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * @inheritdoc
     */
    public function getToDate(): ?string
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setToDate(string $toDate): HolidaysInterface
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription(string $description): HolidaysInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Receive Available Status
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Holidays::class);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
