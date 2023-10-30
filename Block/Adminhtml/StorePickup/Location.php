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

namespace MageINIC\StorePickup\Block\Adminhtml\StorePickup;

use Magento\Backend\Block\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class for CustomPhtml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Location extends Template
{
    public const GOOGLE_MAP_API = 'store_pickup/google_api/google_map_api';

    /**
     * @var string
     */
    protected $_template = 'MageINIC_StorePickup::location.phtml';

    /**
     * Receive Google Map Api Key
     *
     * @return string|null
     */
    public function getGoogleKey(): ?string
    {
        return $this->_scopeConfig->getValue(self::GOOGLE_MAP_API, ScopeInterface::SCOPE_STORE);
    }
}
