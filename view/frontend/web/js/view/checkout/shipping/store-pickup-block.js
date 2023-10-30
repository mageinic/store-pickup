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
define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'jquery',
    "underscore",
    'ko',
    'mage/calendar',
    'mage/translate'
], function (
    Component,
    quote,
    url,
    $,
    _,
    ko,
    calendar,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MageINIC_StorePickup/checkout/shipping/store-pickup-block'
        },
        initialize: function () {
            this._super();
            ko.bindingHandlers.datepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    var options = {minDate: 0};
                    $el.datepicker(options);

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datepicker) {
                            writable = propWriters.datepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datepicker("getDate"));

                },

                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    //when the view model is updated, update the widget
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }
                }
            };

            return this;
        },



        getStorePickup: function () {
            return window.checkoutConfig.pickup_stores;
        },

        getStorePickupList: function () {
            return _.map(this.getStorePickup(), function (value, key) {
                return {
                    'value': key,
                    'type': value
                }
            });
        },

        canVisibleBlock: function () {

            var shippingMethod = quote.shippingMethod();
            if (shippingMethod != null) {
                return shippingMethod.method_code === "mageinic_store_pickup";
            }
        },


        selectStorePickup: function (viewModel, event) {
            var target = event.target;
            var newValue = $(target).val();
            var store_url = this.getStoreUrl();
            if (newValue) {
                $("#calendar-store-pickup").val('');
                $.ajax({
                    dataType: 'json',
                    type: "POST",
                    url: store_url,
                    showLoader: true,
                    data: {ajaxid: 4, entity_id: newValue},
                    success: function (data) {
                        $(".store-data").val(JSON.stringify(data));

                        var storeInfo = data.content;
                        $(".mi-pickup-info").html(storeInfo);
                        $(".calendar-field-container").show();
                        $(".time-field-container").show();

                    }, error: function (xhr) {

                    }
                });
            } else {
                $(".mi-pickup-info").html('');
                $(".calendar-field-container").hide();
                $(".time-field-container").hide();
            }
        },
        getStoreUrl: function () {
            return url.build('store_pickup/index/storeData');
        },

    });
});


