/**
 * Google Maps integration for Vultur Restaurant
 * Handles map initialization and location display
 */

// Restaurant location (default coordinates - replace with actual location)
const RESTAURANT_LOCATION = {
    lat: -34.397,
    lng: -58.644
};

// Map configuration
const MAP_CONFIG = {
    zoom: 15,
    center: RESTAURANT_LOCATION,
    mapTypeId: 'roadmap',
    styles: [
        {
            "featureType": "all",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "weight": "2.00"
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#9c9c9c"
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "labels.text",
            "stylers": [
                {
                    "visibility": "on"
                }
            ]
        },
        {
            "featureType": "landscape",
            "elementType": "all",
            "stylers": [
                {
                    "color": "#f2f2f2"
                }
            ]
        },
        {
            "featureType": "landscape",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#ffffff"
                }
            ]
        },
        {
            "featureType": "landscape.man_made",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#ffffff"
                }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "road",
            "elementType": "all",
            "stylers": [
                {
                    "saturation": -100
                },
                {
                    "lightness": 45
                }
            ]
        },
        {
            "featureType": "road",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#eeeeee"
                }
            ]
        },
        {
            "featureType": "road",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#7b7b7b"
                }
            ]
        },
        {
            "featureType": "road",
            "elementType": "labels.text.stroke",
            "stylers": [
                {
                    "color": "#ffffff"
                }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "simplified"
                }
            ]
        },
        {
            "featureType": "road.arterial",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "transit",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "all",
            "stylers": [
                {
                    "color": "#46bcec"
                },
                {
                    "visibility": "on"
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#c8d7d4"
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#070707"
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "labels.text.stroke",
            "stylers": [
                {
                    "color": "#ffffff"
                }
            ]
        }
    ]
};

let map;
let marker;
let infoWindow;

/**
 * Initialize Google Map
 */
function initMap() {
    // Check if map container exists
    const mapContainer = document.getElementById('map');
    if (!mapContainer) {
        console.log('Map container not found');
        return;
    }

    try {
        // Create map
        map = new google.maps.Map(mapContainer, MAP_CONFIG);

        // Create marker
        marker = new google.maps.Marker({
            position: RESTAURANT_LOCATION,
            map: map,
            title: 'Vultur Restaurant',
            animation: google.maps.Animation.DROP,
            icon: {
                url: createCustomMarkerIcon(),
                scaledSize: new google.maps.Size(50, 50),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(25, 50)
            }
        });

        // Create info window
        infoWindow = new google.maps.InfoWindow({
            content: createInfoWindowContent()
        });

        // Add click listener to marker
        marker.addListener('click', function() {
            infoWindow.open(map, marker);
        });

        // Open info window by default
        infoWindow.open(map, marker);

        // Add map controls
        addMapControls();

        // Handle map resize
        handleMapResize();

        console.log('Map initialized successfully');

    } catch (error) {
        console.error('Error initializing map:', error);
        showMapError();
    }
}

/**
 * Create custom marker icon
 */
function createCustomMarkerIcon() {
    // Create a simple SVG marker with restaurant colors
    const svg = `
        <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
            <circle cx="25" cy="20" r="18" fill="#1e7dd8" stroke="#ffffff" stroke-width="2"/>
            <path d="M25 2 L25 38 M15 12 L35 12 M15 28 L35 28" stroke="#ffffff" stroke-width="2" fill="none"/>
            <circle cx="25" cy="45" r="3" fill="#1e7dd8"/>
        </svg>
    `;
    
    return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
}

/**
 * Create info window content
 */
function createInfoWindowContent() {
    return `
        <div class="map-info-window" style="min-width: 250px; font-family: 'Segoe UI', Arial, sans-serif;">
            <div style="text-align: center; margin-bottom: 10px;">
                <h5 style="color: #1e7dd8; margin: 0; font-weight: 600;">
                    <i class="fas fa-utensils" style="margin-right: 5px;"></i>
                    Vultur Restaurant
                </h5>
            </div>
            
            <div style="margin-bottom: 8px;">
                <i class="fas fa-map-marker-alt" style="color: #1e7dd8; width: 16px;"></i>
                <span style="margin-left: 8px;">Calle Principal #123, Centro, Ciudad</span>
            </div>
            
            <div style="margin-bottom: 8px;">
                <i class="fas fa-phone" style="color: #1e7dd8; width: 16px;"></i>
                <span style="margin-left: 8px;">
                    <a href="tel:+123456789" style="color: #1e7dd8; text-decoration: none;">(123) 456-7890</a>
                </span>
            </div>
            
            <div style="margin-bottom: 8px;">
                <i class="fas fa-clock" style="color: #1e7dd8; width: 16px;"></i>
                <span style="margin-left: 8px;">Lun - Dom: 11:00 AM - 10:00 PM</span>
            </div>
            
            <div style="text-align: center; margin-top: 15px;">
                <button onclick="openDirections()" style="background: #1e7dd8; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-size: 14px;">
                    <i class="fas fa-directions"></i> Cómo llegar
                </button>
            </div>
        </div>
    `;
}

/**
 * Add custom map controls
 */
function addMapControls() {
    // Create control container
    const controlDiv = document.createElement('div');
    controlDiv.style.margin = '10px';

    // Create control button
    const controlButton = document.createElement('div');
    controlButton.style.backgroundColor = '#ffffff';
    controlButton.style.border = '2px solid #ffffff';
    controlButton.style.borderRadius = '3px';
    controlButton.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
    controlButton.style.color = 'rgb(25,25,25)';
    controlButton.style.cursor = 'pointer';
    controlButton.style.fontFamily = 'Roboto,Arial,sans-serif';
    controlButton.style.fontSize = '16px';
    controlButton.style.lineHeight = '38px';
    controlButton.style.margin = '8px 0 22px';
    controlButton.style.padding = '0 5px';
    controlButton.style.textAlign = 'center';
    controlButton.innerHTML = '<i class="fas fa-crosshairs"></i> Mi ubicación';
    controlButton.title = 'Ir a mi ubicación';

    controlDiv.appendChild(controlButton);

    // Add click listener
    controlButton.addEventListener('click', function() {
        getCurrentLocation();
    });

    // Add control to map
    map.controls[google.maps.ControlPosition.TOP_RIGHT].push(controlDiv);
}

/**
 * Get user's current location
 */
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                // Create user marker
                const userMarker = new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: 'Tu ubicación',
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        scaledSize: new google.maps.Size(32, 32)
                    }
                });

                // Adjust map to show both locations
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(RESTAURANT_LOCATION);
                bounds.extend(userLocation);
                map.fitBounds(bounds);

                // Calculate and display distance
                calculateDistance(userLocation);
            },
            function(error) {
                console.error('Geolocation error:', error);
                showLocationError();
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        showLocationError();
    }
}

/**
 * Calculate distance between user and restaurant
 */
function calculateDistance(userLocation) {
    const service = new google.maps.DistanceMatrixService();
    
    service.getDistanceMatrix({
        origins: [userLocation],
        destinations: [RESTAURANT_LOCATION],
        travelMode: google.maps.TravelMode.DRIVING,
        unitSystem: google.maps.UnitSystem.METRIC,
        avoidHighways: false,
        avoidTolls: false
    }, function(response, status) {
        if (status === 'OK') {
            const distance = response.rows[0].elements[0].distance;
            const duration = response.rows[0].elements[0].duration;
            
            if (distance && duration) {
                showDistanceInfo(distance.text, duration.text);
            }
        }
    });
}

/**
 * Show distance information
 */
function showDistanceInfo(distance, duration) {
    const infoDiv = document.createElement('div');
    infoDiv.className = 'alert alert-info position-fixed';
    infoDiv.style.cssText = 'top: 20px; left: 20px; z-index: 1000; max-width: 300px;';
    infoDiv.innerHTML = `
        <i class="fas fa-route"></i>
        <strong>Distancia:</strong> ${distance}<br>
        <strong>Tiempo estimado:</strong> ${duration}
        <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(infoDiv);
    
    // Auto remove after 10 seconds
    setTimeout(() => {
        if (infoDiv.parentNode) {
            infoDiv.remove();
        }
    }, 10000);
}

/**
 * Open directions in Google Maps
 */
function openDirections() {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${RESTAURANT_LOCATION.lat},${RESTAURANT_LOCATION.lng}&destination_place_id=ChIJ&travelmode=driving`;
    window.open(url, '_blank');
}

/**
 * Handle map resize for responsive design
 */
function handleMapResize() {
    window.addEventListener('resize', function() {
        if (map) {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(RESTAURANT_LOCATION);
        }
    });
}

/**
 * Show map error message
 */
function showMapError() {
    const mapContainer = document.getElementById('map');
    if (mapContainer) {
        mapContainer.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 text-center p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px dashed #dee2e6; border-radius: 10px;">
                <div>
                    <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                    <h5 class="text-dark">Nuestra Ubicación</h5>
                    <p class="text-muted mb-3">
                        <strong>Calle Principal #123</strong><br>
                        Centro, Ciudad<br>
                        <i class="fas fa-phone"></i> (123) 456-7890
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="https://www.google.com/maps/search/Calle+Principal+123+Centro+Ciudad" 
                           target="_blank" class="btn btn-primary btn-sm">
                            <i class="fas fa-external-link-alt"></i> Ver en Google Maps
                        </a>
                        <a href="tel:+123456789" class="btn btn-success btn-sm">
                            <i class="fas fa-phone"></i> Llamar
                        </a>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Para ver el mapa interactivo, se requiere configurar la API de Google Maps
                    </small>
                </div>
            </div>
        `;
    }
}

/**
 * Show location error message
 */
function showLocationError() {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-warning position-fixed';
    alertDiv.style.cssText = 'top: 20px; left: 20px; z-index: 1000; max-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i>
        No se pudo obtener tu ubicación. Por favor, permite el acceso a la ubicación o verifica tu configuración.
        <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 8000);
}

/**
 * Initialize map when page loads
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the contact page and if Google Maps is available
    if (document.getElementById('map')) {
        // If Google Maps hasn't loaded yet, wait for it
        if (typeof google === 'undefined') {
            console.log('Waiting for Google Maps to load...');
            
            // Set up a check for Google Maps availability
            let checkCount = 0;
            const checkInterval = setInterval(() => {
                checkCount++;
                if (typeof google !== 'undefined' && google.maps) {
                    clearInterval(checkInterval);
                    initMap();
                } else if (checkCount > 20) { // Stop checking after 10 seconds
                    clearInterval(checkInterval);
                    showMapError();
                }
            }, 500);
        } else {
            initMap();
        }
    }
});

// Make functions available globally for Google Maps callback
window.initMap = initMap;
window.openDirections = openDirections;
