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

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class for CountryName
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CountryName implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $countryCollection;

    /**
     * @var array|null
     */
    private ?array $countries = null;

    /**
     * CountryName Constructor
     *
     * @param CollectionFactory $countryCollection
     */
    public function __construct(
        CollectionFactory $countryCollection
    ) {
        $this->countryCollection = $countryCollection;
    }

    /**
     * Get options
     *
     * @return array|null
     */
    public function toOptionArray(): ?array
    {
        if ($this->countries === null) {
            $this->countries = $this->countryCollection->create()->toOptionArray('');
        }
        return $this->countries;
    }

    /**
     * Receive Option Array
     *
     * @return array
     */
    public function getOptions(): array
    {
        $countryOptions = $this->toOptionArray();
        $finalOption = [];
        foreach ($countryOptions as $option) {
            $finalOption[$option['value']] = $option['label'];
        }

        return $finalOption;
    }
}
