<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Routing\DeliveryPoint;
use App\Routing\Strategies\OSRMFastRouteStrategy;
use App\Routing\RoutingService;

class DeliveryRouteController extends Controller
{
    public function index()
    {
        $points = [
            new DeliveryPoint(6.5450, -73.1310, 'Terminal de Transportes'),
            new DeliveryPoint(6.5535, -73.1340, 'C.C. Camino Real'),
            new DeliveryPoint(6.5545, -73.1335, 'Plaza de Banderas'),
            new DeliveryPoint(6.5580, -73.1315, 'Hospital'),
            new DeliveryPoint(6.5565, -73.1350, 'Parque El Gallineral')
        ];

        $strategy = new OSRMFastRouteStrategy();
        $routingService = new RoutingService($strategy);
        
        $processedPoints = $routingService->executeRouting($points);

        return view('delivery.routing', ['points' => $processedPoints]);
    }
}
