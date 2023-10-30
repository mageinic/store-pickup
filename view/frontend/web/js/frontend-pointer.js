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

// Constants
const METERS_PER_MILE = 1609.34;
const METERS_PER_KILOMETER = 1000;

/**
 * Helper function to get the current day of the week
 *
 * @param {number} date - A Unix timestamp representing the date.
 * @returns {string|null} - The name of the day (e.g., "sunday") or null if invalid input.
 */
function getWeek(date) {
    const daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const dayOfWeek = daysOfWeek[new Date(date).getDay()];
    return dayOfWeek || null;
}

/**
 * Helper function to convert time to a readable format
 *
 * @param {string} time - A time in HH:MM format.
 * @returns {string} - The time in a readable format (e.g., "1:30 pm").
 */
function tConvert(time) {
    const match = time.toString().match(/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/) || [time];

    if (match.length > 1) {
        let [_, hours, minutes] = match.map(Number);
        const period = hours < 12 ? 'am' : 'pm';
        hours = (hours % 12) || 12;
        return `${hours}:${String(minutes).padStart(2, '0')} ${period}`;
    }

    return time;
}

/**
 * Get today's schedule and display it
 *
 * @param {Array} schedule - An array of store schedules.
 */
function getTodaySchedule(schedule) {
    const currentDay = getWeek(Date.now());
    const data = schedule.find(item => item.day.toLowerCase() === currentDay);

    if (data) {
        const {opening_hour, opening_minutes, closing_hour, closing_minutes} = data;
        const openingTime = tConvert(`${opening_hour}:${opening_minutes}`);
        const closingTime = tConvert(`${closing_hour}:${closing_minutes}`);
        const openingHours = `${openingTime} - ${closingTime}`;

        const span = document.createElement('span');
        span.classList.add('mageinic-locator-today-opening-hours');
        span.textContent = openingHours;

        const arrowDownElement = document.querySelector('.mageinic-locator-arrow.-down');
        arrowDownElement.parentNode.insertBefore(span, arrowDownElement);
    }
}

/**
 * Initialize the map with markers
 *
 * @param {Array} markers - An array of marker data.
 * @param {Array} infoWindowContent - An array of info window content.
 * @param {Array} schedule - An array of store schedules (optional).
 * @param {string} length - The unit of length for the radius (e.g., 'km' or 'mi').
 */
function initializeMap(markers, infoWindowContent, length = 'mi', schedule = null) {
    const mapOptions = {
        zoom: 6,
        scrollwheel: true,
        draggable: true,
        mapTypeControl: true,
        panControl: true,
        zoomControl: true,
        scaleControl: true,
        streetViewControl: true,
        overviewMapControl: true,
        rotateControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    const map = new google.maps.Map(document.getElementById("mageinic-canvas-map"), mapOptions);
    const bounds = new google.maps.LatLngBounds();
    const infoWindow = new google.maps.InfoWindow();

    markers.forEach((markerData, i) => {
        const [title, lat, lng] = markerData;
        const position = new google.maps.LatLng(lat, lng);
        bounds.extend(position);

        const marker = new google.maps.Marker({
            position,
            map,
            title
        });

        google.maps.event.addListener(marker, 'click', () => {
            infoWindow.setContent(infoWindowContent[i][0]);
            infoWindow.open(map, marker);
        });
    });

    map.fitBounds(bounds);
    map.setZoom(10);

    if (schedule) {
        getTodaySchedule(schedule);
    } else {

        // Add radius selection logic if applicable
        if (length === 'km' || length === 'mi') {
            addRadiusSelection(map, markers, length);
        }

        // Add search box logic
        addSearchBox(map, markers);
    }
}

/**
 * Add radius selection functionality to the map.
 *
 * @param {google.maps.Map} map - The Google Map instance.
 * @param {Array} markers - An array of marker data.
 * @param {string} length - The unit of length for the radius (e.g., 'km' or 'mi').
 */
function addRadiusSelection(map, markers, length) {
    let circle;

    const radiusSelect = document.getElementById('radius-select');
    const radiusValue = document.querySelector('.radius-value');

    radiusSelect.addEventListener('change', () => {
        const selectedRadius = parseFloat(radiusSelect.value);

        if (circle && circle.setMap) {
            circle.setMap(null);
        }

        const center = map.getCenter();
        const radius = (length === 'km') ? selectedRadius * METERS_PER_KILOMETER : selectedRadius * METERS_PER_MILE;

        circle = new google.maps.Circle({
            center,
            strokeColor: 'rgba(0,72,255,0.26)',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: 'rgba(0,149,255,0.44)',
            fillOpacity: 0.35,
            radius,
            map
        });

        map.fitBounds(circle.getBounds());
    });
}

/**
 * Add search box functionality to the map.
 *
 * @param {google.maps.Map} map - The Google Map instance.
 * @param {Array} markers - An array of marker data.
 */
function addSearchBox(map, markers) {
    const input = document.getElementById('search-text');
    const searchBox = new google.maps.places.SearchBox(input);
   // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    map.addListener('bounds_changed', () => {
        searchBox.setBounds(map.getBounds());
    });

    searchBox.addListener('places_changed', () => {
        const places = searchBox.getPlaces();

        if (places.length === 0) {
            return;
        }

        markers.forEach((marker) => {
            marker.setMap(null);
        });

        const bounds = new google.maps.LatLngBounds();
        places.forEach((place) => {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }

            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }

            if (circle && circle.setMap) {
                circle.setMap(null);
            }

            const radiusSelect = document.getElementById('radius-select');
            const value = radiusSelect.value;
            const radius = (length === 'km') ? value * METERS_PER_KILOMETER : value * METERS_PER_MILE;

            circle = new google.maps.Circle({
                center: place.geometry.location,
                radius,
                map
            });

            map.fitBounds(circle.getBounds());
        });

        map.fitBounds(bounds);
    });
}

