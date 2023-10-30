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

namespace MageINIC\StorePickup\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use MageINIC\StorePickup\Helper\Data as StorePickupHelper;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected ActionFactory $actionFactory;

    /**
     * @var StorePickupHelper
     */
    protected StorePickupHelper $helper;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param StorePickupHelper $helper
     */
    public function __construct(
        ActionFactory     $actionFactory,
        StorePickupHelper $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    /**
     * Validate and Match Store Pickup Page and modify request
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        /** @var Request $request */
        $identifier = trim($request->getPathInfo(), '/');
        $urlSuffix = $this->helper->getUrlSuffix();
        $pos = strpos($identifier, $urlSuffix);

        if ($urlSuffix && $pos !== false) {
            $identifier = substr($identifier, 0, $pos);
        }

        $routePath = explode('/', $identifier);
        $routeSize = count($routePath);

        if (!$this->helper->isStorePickupRoute($routePath, $routeSize)) {
            return null;
        }

        $controller = 'index';
        $action = ($routeSize === 1) ? 'index' : 'view';
        $pathInfo = "/store_pickup/index/$action";
        $key = $routeSize === 2 ? $routePath[1] : null;

        $request->setControllerName($controller)
            ->setActionName($action)
            ->setPathInfo($pathInfo)
            ->setParam('identifier_key', $key);

        return $this->actionFactory->create(Forward::class);
    }
}
