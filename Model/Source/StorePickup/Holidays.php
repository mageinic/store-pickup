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
 * @package MageINIC_StorePickup
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\StorePickup\Model\Source\StorePickup;

use MageINIC\StorePickup\Api\Data\HolidaysInterface;
use MageINIC\StorePickup\Api\HolidaysRepositoryInterface as HolidaysRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Holidays
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Holidays implements OptionSourceInterface
{
    /**
     * @var SortOrderBuilder
     */
    protected SortOrderBuilder $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var HolidaysRepository
     */
    protected HolidaysRepository $holidaysRepository;

    /**
     * Holidays Constructor
     *
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HolidaysRepository $holidaysRepository
     */
    public function __construct(
        SortOrderBuilder      $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HolidaysRepository    $holidaysRepository
    ) {
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->holidaysRepository = $holidaysRepository;
    }

    /**
     * Get options
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $availableOptions = $this->getHolidays();
        $options = [];
        foreach ($availableOptions as $option) {
            $options[] = [
                'label' => $option->getName(),
                'value' => $option->getId(),
            ];
        }
        return $options;
    }

    /**
     * Get Holidays
     *
     * @return HolidaysInterface[]
     * @throws LocalizedException
     */
    private function getHolidays(): array
    {
        $sortOrder = $this->sortOrderBuilder->setField(HolidaysInterface::NAME)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(HolidaysInterface::IS_ACTIVE, true)
            ->addSortOrder($sortOrder)
            ->create();

        $collection = $this->holidaysRepository->getList($searchCriteria);

        return $collection->getItems();
    }
}
