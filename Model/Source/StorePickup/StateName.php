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

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class for StateName
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StateName implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $regionCollection;

    /**
     * @var array|null
     */
    private ?array $regions = null;

    /**
     * StateName Constructor
     *
     * @param CollectionFactory $regionCollection
     */
    public function __construct(
        CollectionFactory $regionCollection
    ) {
        $this->regionCollection = $regionCollection;
    }

    /**
     * Get options
     *
     * @return array|null
     */
    public function toOptionArray(): ?array
    {
        if ($this->regions  === null) {
            $this->regions = $this->regionCollection->create()->toOptionArray('');
        }
        return $this->regions;
    }

    /**
     * Receive Option Array
     *
     * @return array
     */
    public function getOptions(): array
    {
        $stateOptions = $this->toOptionArray();
        $finalOption = [];
        foreach ($stateOptions as $option) {
            $finalOption[$option['value']] = $option['label'];
        }
        return $finalOption;
    }
}
