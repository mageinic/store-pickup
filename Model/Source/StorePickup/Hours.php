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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class for Hours
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Hours implements OptionSourceInterface
{
    /**
     * Receive Options
     *
     * @return array
     */
    public function getOptions(): array
    {
        $lengthOptions = $this->toOptionArray();
        $finalOption = [];
        foreach ($lengthOptions as $option) {
            $finalOption[$option['value']] = $option['label'];
        }
        return $finalOption;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        for ($i = 00; $i < 24; $i++) {
            $hours = str_pad($i, 2, '0', STR_PAD_LEFT);
            $options[] = ['value' => $hours, 'label' => $hours];
        }

        return $options;
    }
}
