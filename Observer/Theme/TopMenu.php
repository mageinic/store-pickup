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

namespace MageINIC\StorePickup\Observer\Theme;

use MageINIC\StorePickup\Helper\Data;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Topmenu as MainTopMenu;

/**
 * Plugin Class Top Menu
 */
class TopMenu implements ObserverInterface
{
    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * TopMenu constructor.
     *
     * @param Data $helperData
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Data $helperData,
        UrlInterface $urlBuilder
    ) {
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Observer Execute.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer): TopMenu
    {
        /** @var MainTopMenu $observer */
        $menu = $observer->getMenu();
        $tree = $menu->getTree();

        $menuLabel = $this->helperData->getTitle();
        $urlKey = $this->helperData->getRoute();

        if ($this->helperData->getPosition() === 'navigation' && $menuLabel && $urlKey) {
            $id = 'store_pickup';
            $currentUrl = $this->urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);

            $data = [
                'name' => $menuLabel,
                'id' => $id,
                'url' => $this->urlBuilder->getUrl($urlKey),
                'is_active' => str_contains($currentUrl, $id)
            ];

            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        }
        return $this;
    }
}
