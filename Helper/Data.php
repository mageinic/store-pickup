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

namespace MageINIC\StorePickup\Helper;

use DateTime;
use MageINIC\StorePickup\Api\StorePickupRepositoryInterface as StorePickupRepository;
use MageINIC\StorePickup\Model\Source\StorePickup\CountryName;
use MageINIC\StorePickup\Model\Source\StorePickup\StateName;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as AuthContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Store\Model\ScopeInterface;

/**
 * Class for Data Helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    public const ENABLE_CONFIG_PATH = 'store_pickup/general/enable';
    public const STORE_LINK_TITLE_PATH = 'store_pickup/general/store_link';
    public const POSITION_CONFIG_PATH = 'store_pickup/general/store_link_location';
    public const URL_CONFIG_PATH = 'store_pickup/store_list_view/frontend_url';

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var AuthContext
     */
    protected AuthContext $authContext;

    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * @var StateName
     */
    private StateName $stateName;

    /**
     * @var CountryName
     */
    private CountryName $countryName;

    /**
     * @var StorePickupRepository
     */
    private StorePickupRepository $storePickupRepository;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param AuthContext $authContext
     * @param FilterManager $filterManager
     * @param StateName $stateName
     * @param CountryName $countryName
     * @param StorePickupRepository $storePickupRepository
     */
    public function __construct(
        Context               $context,
        CustomerSession       $customerSession,
        AuthContext           $authContext,
        FilterManager         $filterManager,
        StateName             $stateName,
        CountryName           $countryName,
        StorePickupRepository $storePickupRepository
    ) {
        $this->customerSession = $customerSession;
        $this->authContext = $authContext;
        $this->filterManager = $filterManager;
        $this->stateName = $stateName;
        $this->countryName = $countryName;
        $this->storePickupRepository = $storePickupRepository;
        parent::__construct($context);
    }

    /**
     * Receive Functionality is Enabled or not
     *
     * @return bool
     */
    public function isEnable(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::ENABLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Trim Store Pickup Content.
     *
     * @param string|null $content
     * @return string
     */
    public function trimContent(?string $content): string
    {
        if ($content) {
            $content = $this->filterManager->stripTags($content);
            $pos = strpos($content, "\n", 0) ?: 100;
            return substr($content, 0, $pos) . '...';
        }

        return '';
    }

    /**
     * Receive Region from Region/State id
     *
     * @param int $regionId
     * @return string
     */
    public function getRegionByCode(int $regionId): string
    {
        $stateOptions = $this->stateName->getOptions();
        return $stateOptions[$regionId];
    }

    /**
     * Receive Country by Country code
     *
     * @param string $code
     * @return string
     */
    public function getCountryByCode(string $code): string
    {
        $countryOptions = $this->countryName->getOptions();
        return $countryOptions[$code];
    }

    /**
     * Receive title for store pickup URL.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->scopeConfig->getValue(self::STORE_LINK_TITLE_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Receive Store Pickup Link Position
     *
     * @return string
     */
    public function getPosition(): string
    {
        return (string) $this->scopeConfig->getValue(self::POSITION_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Is StorePickup Router is Available.
     *
     * @param array $routePath
     * @param int $routeSize
     * @return bool
     */
    public function isStorePickupRoute(array $routePath, int $routeSize): bool
    {
        if ($routeSize > 3) {
            return false;
        }
        $storePickupRoute = rtrim($this->getRoute(), $this->getUrlSuffix() ?: '');

        return $routePath[0] === $storePickupRoute;
    }

    /**
     * Receive route name for store pickup. (If empty, default 'store_pickup' will be used)
     *
     * @return string
     */
    public function getRoute(): string
    {
        $route = $this->scopeConfig->getValue(self::URL_CONFIG_PATH, ScopeInterface::SCOPE_STORE);

        return $this->formatUrlKey($route);
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey(string $str): string
    {
        return $this->filterManager->translitUrl($str);
    }

    /**
     * Receive category rewrite suffix for store
     *
     * @return string
     */
    public function getUrlSuffix(): string
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Format a time range and escape it
     *
     * @param int $hour
     * @param int $minutes
     * @return string
     */
    public function formatTime(int $hour, int $minutes): string
    {
        $formatTime = fn($h, $m) => (new DateTime("$h:$m"))->format('g:i a');
        return $formatTime($hour, $minutes);
    }

    /**
     * Receive Store Pickup Address
     *
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getPickupStoreAddress(int $storeId): array
    {
        $store = $this->storePickupRepository->getById($storeId);

        return [
            'firstname' => $store->getName(),
            'lastname' => '',
            'street' => $store->getAddress(),
            'city' => $store->getCity(),
            'country_id' => $store->getCountry(),
            'region_id' => $store->getStateId(),
            'region' => $store->getRegion(),
            'postcode' => $store->getPostcode(),
            'telephone' => $store->getContactNo(),
            'fax' => '',
            'save_in_address_book' => 1
        ];
    }
}
