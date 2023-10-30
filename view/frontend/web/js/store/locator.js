/*
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

define([
    'jquery',
    'jquery/jquery-ui',
    'jquery-ui-modules/widget',
    'mapAPI',
    'pointer'
], function ($) {
    'use strict';

    $.widget('mageinic.mapLocator', {
        options: {
            "length": '',
            "schedule": '',
        },

        selectors: {
            'toggleSelector': '[data-toggle="toggle"]',
            'parentToggleSelector': '.tbl-accordion-body'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            var self = this;

            if (self.options.length === '') {
                $(document).ready(function () {
                    $(self.selectors.toggleSelector).click(function () {
                        $(this).parents().next(self.selectors.parentToggleSelector).toggle();
                    });
                });
            }

            initializeMap(
                self.options.markers,
                self.options.infoWindowContent,
                self.options.length,
                self.options.schedule
            );

        }
    });

    return $.mageinic.mapLocator;
});
