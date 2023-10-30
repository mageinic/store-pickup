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
    'jquery/ui',
], function ($) {
    'use strict';

    $.widget('mageinic.miStorePickup', {
        options: {
            mapSelector: 'map-canvas',
            zoom: 5,
            latitudeSelector: '[name="latitude"]',
            longitudeSelector: '[name="longitude"]',
            countrySelector: 'select[name="country_id"]',
            regionSelector: '[name="region"]',
            citySelector: '[name="city"]',
            addressSelector: '[name="address"]',
            postcodeSelector: '[name="postcode"]'
        },

        /**
         * @private
         */
        _create: function () {
            var self = this;

            $(this.options.latitudeSelector).add(this.options.longitudeSelector).keyup(function () {
                self._initMap();
            });

            $('#add-coordinates').click(function () {
                self.findStoreByAddress();
            });

            self._initMap();
        },

        /**
         * Init Google map
         *
         * @returns {mageinic.miStorePickup}
         * @private
         */
        _initMap: function () {
            var lat = parseFloat($(this.options.latitudeSelector).val());
            var long = parseFloat($(this.options.latitudeSelector).val());

            this.latlng = new google.maps.LatLng(lat, long);

            var mapOptions = {
                zoom: this.options.zoom,
                center: this.latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            this.map = new google.maps.Map(document.getElementById(this.options.mapSelector), mapOptions);

            this.marker = new google.maps.Marker({
                position: this.latlng,
                draggable: true,
                map: this.map
            });

            google.maps.event.addListener(this.marker, 'dragend', function (event) {
                this._updateLatLngFields(event.latLng.lat(), event.latLng.lng());
            }.bind(this));

            return this;
        },

        /**
         * Find Store Pickup By Address
         */
        findStoreByAddress: function () {

            var queryList = [];
            queryList.push($(this.options.countrySelector).val());
            if (!$(this.options.regionSelector).is(':disabled')) {
                queryList.push($(this.options.regionSelector).attr('title'));
            }
            queryList.push($(this.options.citySelector).val());
            queryList.push($(this.options.addressSelector).val());

            var request = {
                query: queryList.join(' ')
            };

            this.service = new google.maps.places.PlacesService(this.map);
            this.service.textSearch(request, this._showSearchResult.bind(this));
        },

        /**
         * Show Search Result
         *
         * @param {array} results
         * @param {string} status
         * @private
         */
        _showSearchResult: function (results, status) {

            if (status === google.maps.places.PlacesServiceStatus.OK && results.length > 0) {
                this.latlng = new google.maps.LatLng(
                    results[0].geometry.location.lat(), results[0].geometry.location.lng()
                );

                this.marker.setPosition(this.latlng);
                this.map.panTo(this.latlng);
                this.map.setZoom(15);
                this.map.setZoom(16);
                this._updateLatLngFields(results[0].geometry.location.lat(), results[0].geometry.location.lng());

                $(this.options.messageSelector).hide();
            } else {
                console.log(status)
            }
        },

        /**
         * update latitude, longitude field
         *
         * @param {float} latitude
         * @param {float} longitude
         * @private
         */
        _updateLatLngFields: function (latitude, longitude) {
            $(this.options.latitudeSelector).val(latitude);
            $(this.options.longitudeSelector).val(longitude);
        }
    });

    return $.mageinic.miStorePickup;
});
