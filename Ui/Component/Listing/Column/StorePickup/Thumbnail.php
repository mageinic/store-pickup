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
namespace MageINIC\StorePickup\Ui\Component\Listing\Column\StorePickup;

use MageINIC\StorePickup\Model\StorePickup\DataProcessor;
use MageINIC\StorePickup\Model\StorePickup\Image;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * StorePickup Class Thumbnail
 */
class Thumbnail extends Column
{
    public const ALT_FIELD = 'name';

    /**
     * @var DataProcessor
     */
    private DataProcessor $dataProcessor;

    /**
     * @var Image
     */
    private Image $storePickupImage;

    /**
     * @var ImageFactory
     */
    protected ImageFactory $imageFactory;

    /**
     * Thumbnail Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param DataProcessor $dataProcessor
     * @param Image $storePickupImage
     * @param ImageFactory $imageFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        DataProcessor      $dataProcessor,
        Image              $storePickupImage,
        ImageFactory       $imageFactory,
        array              $components = [],
        array              $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->storePickupImage = $storePickupImage;
        $this->imageFactory = $imageFactory;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['image'])) {
                    $model = $this->dataProcessor->dataObjectProcessor($item);
                    $placeholder = $this->imageFactory->create();
                    $url = $model->getImage()
                        ? $this->storePickupImage->getUrl($model)
                        : $placeholder->getDefaultPlaceholderUrl('image');
                    $item[$fieldName . '_src'] = $url;
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        Actions::URL_PATH_EDIT,
                        ['entity_id' => $item['entity_id']]
                    );

                    $item[$fieldName . '_orig_src'] = $url;
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     * @return null|string
     */
    protected function getAlt(array $row): ?string
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}
