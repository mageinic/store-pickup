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

namespace MageINIC\StorePickup\Model\StorePickup;

use MageINIC\StorePickup\Api\Data\StorePickupInterface as StorePickup;
use Magento\Framework\Filter\FilterManager;

/**
 * Class for Url Path Generator.
 *
 * @api
 */
class UrlPathGenerator
{
    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * UrlPathGenerator Constructor
     *
     * @param FilterManager $filterManager
     */
    public function __construct(
        FilterManager $filterManager
    ) {
        $this->filterManager = $filterManager;
    }

    /**
     * Receive Url Path
     *
     * @param StorePickup $storePickup
     * @return string
     */
    public function getUrlPath(StorePickup $storePickup): string
    {
        return $storePickup->getIdentifier();
    }

    /**
     * Generate Store Pickup page url key based on url_key entered by merchant or Store Pickup Name
     *
     * @param StorePickup $storePickup
     * @return string
     */
    public function generateUrlKey(StorePickup $storePickup): string
    {
        $urlKey = $storePickup->getIdentifier();
        return $this->filterManager->translitUrl(
            $urlKey === '' || $urlKey === null ? $storePickup->getName() : $urlKey
        );
    }
}
