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

namespace MageINIC\StorePickup\Model\Source;

use Magento\Directory\Model\AllowedCountries as Countries;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class for AllowedCountries
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AllowedCountries implements OptionSourceInterface
{
    /**
     * @var Countries
     */
    protected Countries $allowCountries;

    /**
     * @var Collection
     */
    protected Collection $countryCollection;

    /**
     * @var string[]|null
     */
    protected ?array $_options = null;

    /**
     * @param Countries $allowedCountryModel
     * @param Collection $countryCollection
     */
    public function __construct(
        Countries  $allowedCountryModel,
        Collection $countryCollection
    ) {
        $this->allowCountries = $allowedCountryModel;
        $this->countryCollection = $countryCollection;
    }

    /**
     * Receive Option Array
     *
     * @return array|null
     */
    public function toOptionArray(): ?array
    {
        $foregroundCountries = '';
        $allowedCounties = $this->allowCountries->getAllowedCountries();
        $this->_options = $this->countryCollection->addFieldToFilter('country_id', $allowedCounties)->loadData()
            ->setForegroundCountries($foregroundCountries)->toOptionArray(true);
        return $this->_options;
    }
}
