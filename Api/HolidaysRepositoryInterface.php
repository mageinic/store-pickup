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

namespace MageINIC\StorePickup\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageINIC\StorePickup\Api\Data\HolidaysInterface;

/**
 * Interface HolidaysRepositoryInterface
 */
interface HolidaysRepositoryInterface
{
    /**
     * Save StorePickup
     *
     * @param \MageINIC\StorePickup\Api\Data\HolidaysInterface $holidays
     * @return \MageINIC\StorePickup\Api\Data\HolidaysInterface
     * @throws LocalizedException
     */
    public function save(HolidaysInterface $holidays): HolidaysInterface;

    /**
     * Retrieve StorePickup matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageINIC\StorePickup\Api\Data\HolidaysSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve store Holidays
     *
     * @param int $id
     * @return \MageINIC\StorePickup\Api\Data\HolidaysInterface
     * @throws LocalizedException
     */
    public function getById(int $id): HolidaysInterface;

    /**
     * Delete store Holidays
     *
     * @param \MageINIC\StorePickup\Api\Data\HolidaysInterface $holidays
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(HolidaysInterface $holidays): bool;

    /**
     * Delete Holidays by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $id): bool;
}
