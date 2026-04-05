<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optimizador de Rutas San Gil</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
        }
        #map {
            width: 100vw;
            height: 100vh;
            z-index: 1;
        }
        .floating-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            color: white;
            width: 320px;
            transition: all 0.3s ease;
        }
        .floating-panel:hover {
            box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.6), 0 10px 12px -6px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .floating-panel h2 {
            margin: 0 0 20px 0;
            font-size: 1.25rem;
            font-weight: 600;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-calculate {
            width: 100%;
            padding: 12px 16px;
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.3), 0 0 15px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, #2563eb, #4338ca);
        }
        .btn-calculate:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>

    <div id="map"></div>

    <div class="floating-panel">
        <h2>Optimizador de Rutas San Gil</h2>
        <button id="calcBtn" class="btn-calculate">Calcular Ruta Óptima</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script>
        const pointsData = @json($points);
        
        const map = L.map('map', {
            zoomControl: false
        }).setView([6.5515, -73.1330], 15);

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        let routingControl = null;
        let markers = [];

        document.getElementById('calcBtn').addEventListener('click', function() {
            if (routingControl) {
                map.removeControl(routingControl);
            }
            
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            const waypoints = pointsData.map(point => {
                const marker = L.marker([point.lat, point.lng])
                    .bindPopup(`<strong style="color:#0f172a;">${point.name}</strong>`)
                    .addTo(map);
                markers.push(marker);
                return L.latLng(point.lat, point.lng);
            });

            routingControl = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                addWaypoints: false,
                fitSelectedRoutes: true,
                show: false,
                lineOptions: {
                    styles: [{color: '#38bdf8', opacity: 0.8, weight: 5}]
                },
                createMarker: function() { return null; }
            }).addTo(map);
        });
    </script>
</body>
</html>
