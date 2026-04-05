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
            background-clip: text;
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
        .loading-overlay {
            display: none;
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.9);
            padding: 12px 24px;
            border-radius: 50px;
            border: 1px solid #38bdf8;
            color: white;
            z-index: 2000;
            font-size: 0.9rem;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #38bdf8;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .badge-demo {
            background: #f59e0b;
            color: #000;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: 8px;
            display: none;
        }
    </style>
</head>
<body>

    <div id="map"></div>

    <div class="floating-panel">
        <h2>Optimizador de Rutas <span id="demoBadge" class="badge-demo">MODO DEMO</span></h2>
        <p style="font-size: 0.85rem; color: #94a3b8; margin-top: -15px; margin-bottom: 20px;">San Gil, Santander</p>
        <button id="calcBtn" class="btn-calculate">Calcular Ruta Óptima</button>
    </div>

    <div id="loading" class="loading-overlay">
        <div class="spinner"></div>
        <span>Calculando ruta inteligente...</span>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script id="points-data" type="application/json">
        {!! json_encode($points) !!}
    </script>
    <script>
        const pointsData = JSON.parse(document.getElementById('points-data').textContent);
        const map = L.map('map', { zoomControl: false }).setView([6.5515, -73.1330], 15);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        let activeLayers = [];

        function decode(str) {
            var index = 0, lat = 0, lng = 0, coordinates = [], shift = 0, result = 0, byte = null, latitude_change, longitude_change, factor = 1e5;
            while (index < str.length) {
                byte = null; shift = 0; result = 0;
                do { byte = str.charCodeAt(index++) - 63; result |= (byte & 0x1f) << shift; shift += 5; } while (byte >= 0x20);
                latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1)); lat += latitude_change;
                byte = null; shift = 0; result = 0;
                do { byte = str.charCodeAt(index++) - 63; result |= (byte & 0x1f) << shift; shift += 5; } while (byte >= 0x20);
                longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1)); lng += longitude_change;
                coordinates.push([lat / factor, lng / factor]);
            }
            return coordinates;
        }

        document.getElementById('calcBtn').addEventListener('click', async function() {
            activeLayers.forEach(l => map.removeLayer(l));
            activeLayers = [];
            
            const pointsList = [...pointsData];
            pointsList.forEach(p => {
                const m = L.marker([p.lat, p.lng]).bindPopup(`<strong>${p.name}</strong>`).addTo(map);
                activeLayers.push(m);
            });

            document.getElementById('loading').style.display = 'flex';
            
            let geometry = null;
            try {
                const res = await fetch(`/api/optimize?coords=${encodeURIComponent(pointsList.map(p => `${p.lng},${p.lat}`).join(';'))}`);
                const data = await res.json();
                if (data.code === 'Ok' && data.trips && data.trips[0].geometry) {
                    geometry = data.trips[0].geometry;
                } else { throw new Error(); }
            } catch (e) {
                try {
                    const fallback = await fetch('/fallback_route.json');
                    const d = await fallback.json();
                    geometry = d.trips[0].geometry;
                    document.getElementById('demoBadge').style.display = 'inline-block';
                } catch (err) {}
            } finally {
                document.getElementById('loading').style.display = 'none';
                if (geometry) {
                    const line = L.polyline(decode(geometry), { color: '#3b82f6', weight: 6, opacity: 1, lineJoin: 'round' }).addTo(map);
                    activeLayers.push(line);
                    map.fitBounds(line.getBounds(), { padding: [80, 80] });
                }
            }
        });
    </script>
</body>
</html>
