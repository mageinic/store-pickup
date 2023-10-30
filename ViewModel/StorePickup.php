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

namespace MageINIC\StorePickup\ViewModel;

use DateTime;
use Exception;
use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Helper\Data;
use MageINIC\StorePickup\Model\ResourceModel\StorePickup\Collection;
use MageINIC\StorePickup\Model\StorePickup\Image;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\Template;
use Magento\Framework\Serialize\SerializerInterface as Serializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ViewModel class for StorePickup.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorePickup implements ArgumentInterface
{
    public const GOOGLE_MAP_API_PATH = 'store_pickup/google_api/google_map_api';
    public const DEFAULT_RADIUS_VALUE_PATH = 'store_pickup/search_config/default_radius';
    public const MAX_RADIUS_VALUE_PATH = 'store_pickup/search_config/max_radius';
    public const RADIUS_LENGTH_NAME_PATH = 'store_pickup/search_config/length_option';

    /**
     * @var ScopeConfig
     */
    private ScopeConfig $scopeConfig;

    /**
     * @var Image
     */
    private Image $storePickupImage;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @var Template
     */
    private Template $filterProvider;

    /**
     * StorePickup Constructor
     *
     * @param ScopeConfig $scopeConfig
     * @param Image $storePickupImage
     * @param Serializer $serializer
     * @param Escaper $escaper
     * @param Data $helperData
     * @param Template $filterProvider
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        Image       $storePickupImage,
        Serializer  $serializer,
        Escaper     $escaper,
        Data        $helperData,
        Template    $filterProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storePickupImage = $storePickupImage;
        $this->serializer = $serializer;
        $this->escaper = $escaper;
        $this->helperData = $helperData;
        $this->filterProvider = $filterProvider;
    }

    /**
     * Static block $content
     *
     * @param string $content
     * @return string
     * @throws Exception
     */
    public function getContentFromHtml(string $content): string
    {
        return $this->filterProvider->filter($content);
    }

    /**
     * Receive Google Map API.
     *
     * @return string
     */
    public function getGoogleKey(): string
    {
        return $this->scopeConfig->getValue(self::GOOGLE_MAP_API_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Receive Google Markers.
     *
     * @param Collection|StorePickupInterface $data
     * @return array
     */
    public function getGoogleMarkers(StorePickupInterface|Collection $data): array
    {
        $data = $data instanceof StorePickupInterface ? [$data] : $data->getData();

        return array_map(function ($store) {
            /** @var StorePickupInterface $store */
            return [
                $store['name'],
                (float)$store['latitude'],
                (float)$store['longitude']
            ];
        }, $data);
    }

    /**
     * Receive Html Separator
     *
     * @return string
     */
    public function getHtmlSeparator(): string
    {
        return "<div class='mageinic-locator-block -separator'><hr class='hr'></div>";
    }

    /**
     * Receive Schedule List with Html
     *
     * @param StorePickupInterface $model
     * @return string
     */
    public function getScheduleList(StorePickupInterface $model): string
    {
        $scheduleList = $this->serializer->unserialize($model->getSchedule());

        return implode('', array_map(function ($schedule) {
            $day = $this->escaper->escapeHtml(ucfirst($schedule['day']));
            $close = $this->escaper->escapeHtml(__('Closed'));

            $timeRange = $this->helperData->formatTime($schedule['opening_hour'], $schedule['opening_minutes']) .
                ' - ' . $this->helperData->formatTime($schedule['closing_hour'], $schedule['closing_minutes']);
            $range = $this->escaper->escapeHtml($timeRange);

            $breakText = $this->escaper->escapeHtml(__('Break Time'));
            $breakTime = $this->helperData->formatTime($schedule['start_break_hour'], $schedule['start_break_minutes'])
                . ' - ' . $this->helperData->formatTime($schedule['end_break_hour'], $schedule['end_break_minutes']);
            $break = $this->escaper->escapeHtml($breakTime);

            $statusHtml = $schedule['is_active']
                ? "<span class=\"mageinic-locator-cell -time\">{$range}</span>
                   <span class=\"mageinic-locator-cell\">{$breakText}</span>
                   <span class=\"mageinic-locator-cell -time\">{$break}</span>"
                : "<span class=\"mageinic-locator-cell -time\">{$close}</span>";

            return <<<HTML
                <div class="mageinic-locator-row">
                    <span class="mageinic-locator-cell -day">{$day}</span>{$statusHtml}
                </div>
            HTML;
        }, $scheduleList));
    }

    /**
     * Change the Date Format of Dates
     *
     * @param string $inputDate
     * @return string
     */
    public function changeDateFormat(string $inputDate): string
    {
        return date("D, d M Y", strtotime($inputDate));
    }

    /**
     * Receive Country by Country code
     *
     * @param string $code
     * @return string
     */
    public function getCountryByCode(string $code): string
    {
        return $this->helperData->getCountryByCode($code);
    }

    /**
     * Receive Google Markers Details With Html.
     *
     * @param StorePickupInterface|Collection $data
     * @return array
     * @throws LocalizedException
     */
    public function getGoogleMarkersDetails(StorePickupInterface|Collection $data): array
    {
        $infoWindowContent = [];
        $pickupStores = $data instanceof StorePickupInterface ? [$data] : $data->getItems();

        foreach ($pickupStores as $pickupStore) {
            /** @var StorePickupInterface $pickupStore */
            $infoWindowContent[] = $this->getStoreInfo($pickupStore);
        }

        return $infoWindowContent;
    }

    /**
     * Receive Store Pickup info window
     *
     * @param StorePickupInterface $pickupStore
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStoreInfo(StorePickupInterface $pickupStore): array
    {
        $addressText = $this->escaper->escapeHtml(__('Address:'));
        $stateText = $this->escaper->escapeHtml(__('State:'));
        $cityText = $this->escaper->escapeHtml(__('City:'));
        $zipText = $this->escaper->escapeHtml(__('Zip:'));
        $descriptionText = $this->escaper->escapeHtml(__('Description:'));

        $image = $this->getStoreImageUrl($pickupStore);
        $name = $pickupStore->getName();
        $url = $pickupStore->getUrl();
        $address = $pickupStore->getAddress();
        $region = $pickupStore->getRegion();
        $city = $pickupStore->getCity();
        $postcode = $pickupStore->getPostcode();
        $content = $this->helperData->trimContent($pickupStore->getContent());

        return [
            "<div class='store-marker-window' style='width: 250px'>
                <h3 class='mi-pickup-name'>
                    <div class='mi-pickup-title'>
                        <a class='mi-pickup-link' href='{$this->escaper->escapeUrl($url)}'
                            title='{$this->escaper->escapeHtmlAttr($name)}'
                            target='_blank' tabindex='0'>{$this->escaper->escapeHtml($name)}</a>
                    </div>
                </h3>
                <div class='mi-pickup-image'>
                    <div class='mi-pickup-image'>
                        <img style='max-width: 100%' src='{$this->escaper->escapeUrl($image)}'
                            alt='{$this->escaper->escapeHtml($name)}'/>
                    </div>
                </div>
                <div class='mi-pickup-address'>
                    <span><strong>{$addressText}</strong>{$this->escaper->escapeHtml($address)}</span>
                    <span><strong>{$stateText}</strong>{$this->escaper->escapeHtml($region)}</span>
                    <span><strong>{$cityText}</strong>{$this->escaper->escapeHtml($city)}</span>
                    <span><strong>{$zipText}</strong>{$this->escaper->escapeHtml($postcode)}</span>
                    <span><strong>{$descriptionText}</strong></span>
                </div>
                <div class='mi-pickup-description'>{$this->escaper->escapeHtml($content)}
                    <a href='{$this->escaper->escapeUrl($url)}'
                        title='read more' target='_blank'>{$this->escaper->escapeHtml(__('Read More'))}</a>
                </div><br>
            </div>"
        ];
    }

    /**
     * Receive Store Pickup Image Url.
     *
     * @param StorePickupInterface $model
     * @return string
     * @throws LocalizedException
     */
    public function getStoreImageUrl(StorePickupInterface $model): string
    {
        return $this->storePickupImage->getUrl($model);
    }

    /**
     * Serialize data into string
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Receive Default Radius value
     *
     * @return int
     */
    public function getDefaultRadius(): int
    {
        return (int)$this->scopeConfig->getValue(self::DEFAULT_RADIUS_VALUE_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Receive Max Radius value
     *
     * @return int
     */
    public function getMaxRadius(): int
    {
        return (int)$this->scopeConfig->getValue(self::MAX_RADIUS_VALUE_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Receive Radius Length Params
     *
     * @return string
     */
    public function getRadiusLength(): string
    {
        return $this->scopeConfig->getValue(self::RADIUS_LENGTH_NAME_PATH, ScopeInterface::SCOPE_STORE);
    }
}
