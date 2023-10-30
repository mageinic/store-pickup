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
 * @package MageINIC_StorePickUp
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

/*global define,alert*/
define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    createShippingAddress,
    selectShippingAddress,
    checkoutData,
) {
    'use strict';

    return {
        saveShippingInformation: function () {
            var payload,
                addressData,
                newShippingAddress;

            var method = quote.shippingMethod().method_code;
            if (method === "mageinic_store_pickup") {
                addressData = quote.shippingAddress();
                //create new shipping address form the store address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());

                var pickupData = jQuery('.store-data').val();
                var storeData = jQuery.parseJSON(pickupData);
                quote.shippingAddress().customerAddressId = 0;
                quote.shippingAddress().firstname = quote.shippingAddress().firstname
                quote.shippingAddress().lastname = quote.shippingAddress().lastname;
                quote.shippingAddress().street[0] = "Store: " + storeData.name;
                quote.shippingAddress().street[1] = storeData.address;
                quote.shippingAddress().city = storeData.city;
                quote.shippingAddress().countryId = storeData.country_id;
                quote.shippingAddress().postcode = storeData.postcode;
                quote.shippingAddress().telephone = storeData.contact_no;
                if (storeData.state) {
                    quote.shippingAddress().regionCode = storeData.state_code;
                    quote.shippingAddress().regionId = storeData.state_id;
                } else {
                    quote.shippingAddress().region = storeData.region;
                }

                payload = {
                    addressInformation: {
                        'shipping_address': quote.shippingAddress(),
                        'billing_address': quote.billingAddress(),
                        'shipping_method_code': quote.shippingMethod().method_code,
                        'shipping_carrier_code': quote.shippingMethod().carrier_code,
                        extension_attributes: {
                            store_pickup_id: jQuery('[name="store-pickup-id"]').val(),
                            store_pickup_date: jQuery('[name="calendar-store-pickup"]').val(),
                        }
                    }
                };
            } else {
                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        'shipping_address': quote.shippingAddress(),
                        'billing_address': quote.billingAddress(),
                        'shipping_method_code': quote.shippingMethod()['method_code'],
                        'shipping_carrier_code': quote.shippingMethod()['carrier_code'],
                        extension_attributes: {
                            store_pickup_id: '',
                            store_pickup_date: '',
                        }
                    }
                };
            }

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    fullScreenLoader.stopLoader();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        }
    };
});
