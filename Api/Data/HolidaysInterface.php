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

/**
 * Interface Store Holidays Interface
 *
 * @api
 */
interface HolidaysInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const HOLIDAY_ID  = 'holiday_id';
    public const NAME        = 'name';
    public const IS_ACTIVE   = 'is_active';
    public const FROM_DATE   = 'from_date';
    public const TO_DATE     = 'to_date';
    public const DESCRIPTION = 'description';
    /**#@-*/

    /**
     * Receive Holiday ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Set Holiday ID
     *
     * @param int $id
     * @return HolidaysInterface
     */
    public function setId(int $id): HolidaysInterface;

    /**
     * Receive holiday name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set holiday name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): HolidaysInterface;

    /**
     * Receive holiday status
     *
     * @return bool|null
     */
    public function getStatus(): ?bool;

    /**
     * Set holiday status
     *
     * @param bool $status
     * @return $this
     */
    public function setStatus(bool $status): HolidaysInterface;

    /**
     * Receive holiday from date
     *
     * @return string|null
     */
    public function getFromDate(): ?string;

    /**
     * Set holiday from date
     *
     * @param string $fromDate
     * @return $this
     */
    public function setFromDate(string $fromDate): HolidaysInterface;

    /**
     * Receive holiday to date
     *
     * @return string|null
     */
    public function getToDate(): ?string;

    /**
     * Set holiday to date
     *
     * @param string $toDate
     * @return $this
     */
    public function setToDate(string $toDate): HolidaysInterface;

    /**
     * Receive holiday description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Set holiday description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): HolidaysInterface;
}
