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

namespace MageINIC\StorePickup\Ui\Component\Listing\Column;

use MageINIC\StorePickup\Api\StorePickupRepositoryInterface as StorePickupRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use MageINIC\StorePickup\Model\StorePickupFactory;

/**
 * Class for StoreName
 */
class StoreName extends Column
{
    public const FIELD_NAME = 'store_pickup_id';

    /**
     * @var StorePickupRepository
     */
    private StorePickupRepository $storePickupRepository;

    /**
     * @param StorePickupRepository $storePickupRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        StorePickupRepository $storePickupRepository,
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        array                 $components = [],
        array                 $data = []
    ) {
        $this->storePickupRepository = $storePickupRepository;
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
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[self::FIELD_NAME] = (!empty($item[self::FIELD_NAME]))
                    ? $this->getStorePickupName($item[self::FIELD_NAME]) : "";

            }
        }
        return $dataSource;
    }

    /**
     * Get Store name by ID
     *
     * @param int $id
     * @return string
     * @throws LocalizedException
     */
    protected function getStorePickupName(int $id): string
    {
        $data = $this->storePickupRepository->getById($id);
        return $data->getName();
    }
}
