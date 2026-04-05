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
        let initialPath = null;
        let markers = [];

        document.getElementById('calcBtn').addEventListener('click', async function() {
            if (routingControl) {
                map.removeControl(routingControl);
            }
            if (initialPath) {
                map.removeLayer(initialPath);
            }
            
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            // 1. Dibujamos los marcadores iniciales sin conectar
            pointsData.forEach(point => {
                const marker = L.marker([point.lat, point.lng])
                    .bindPopup(`<strong style="color:#0f172a;">${point.name}</strong>`)
                    .addTo(map);
                markers.push(marker);
            });

            // 2. Trazamos línea de fallback temporal para evidenciar el intento de cálculo
            const tempWaypoints = pointsData.map(p => L.latLng(p.lat, p.lng));
            initialPath = L.polyline(tempWaypoints, {
                color: '#38bdf8', weight: 4, dashArray: '10, 10'
            }).addTo(map);
            map.fitBounds(initialPath.getBounds(), { padding: [50, 50] });

            try {
                // 3. OPTIMIZACIÓN REAL (TSP - Traveling Salesperson Problem)
                // Utilizamos el servicio "trip" de OSRM para reordenar los puntos y encontrar la ruta más rápida
                const coordsString = pointsData.map(p => `${p.lng},${p.lat}`).join(';');
                const tripUrl = `https://routing.openstreetmap.de/routed-car/trip/v1/driving/${coordsString}?source=first&roundtrip=false`;
                
                const response = await fetch(tripUrl);
                const data = await response.json();

                let optimizedPoints = [...pointsData];

                if (data.code === 'Ok' && data.waypoints) {
                    // Ordenamos nuestros puntos basados en el waypoint_index óptimo calculado por OSRM
                    const ordered = new Array(pointsData.length);
                    data.waypoints.forEach((wp, index) => {
                        ordered[wp.waypoint_index] = pointsData[index];
                    });
                    optimizedPoints = ordered;
                } else {
                    console.warn('No se pudo optimizar la ruta con OSRM Trip, se mantendrá el orden original.');
                }

                const optimizedWaypoints = optimizedPoints.map(p => L.latLng(p.lat, p.lng));

                // 4. Trazar la ruta "real" optimizada por calles usando Leaflet Routing Machine
                routingControl = L.Routing.control({
                    waypoints: optimizedWaypoints,
                    router: L.Routing.osrmv1({
                        serviceUrl: 'https://routing.openstreetmap.de/routed-car/route/v1'
                    }),
                    routeWhileDragging: false,
                    addWaypoints: false,
                    fitSelectedRoutes: true,
                    show: false, // Ocultamos el panel de texto
                    lineOptions: {
                        styles: [{color: '#10b981', opacity: 0.9, weight: 6}]
                    },
                    createMarker: function() { return null; }
                }).on('routesfound', function() {
                    // Removemos la línea temporal si se trazaron las calles exitosamente
                    if (initialPath) map.removeLayer(initialPath);
                }).on('routingerror', function(e) {
                    console.warn('Fallo el trazado de calles por OSRM, manteniendo línea de respaldo.', e);
                }).addTo(map);

            } catch (error) {
                console.error("Error al optimizar u obtener la ruta:", error);
            }
        });
    </script>
</body>
</html>
