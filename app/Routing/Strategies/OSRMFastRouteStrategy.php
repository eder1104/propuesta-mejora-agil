<?php

namespace App\Routing\Strategies;

use App\Routing\Contracts\RouteStrategyInterface;
use App\Routing\PointIterator;

class OSRMFastRouteStrategy implements RouteStrategyInterface
{
    public function calculateRoute(array $points): array
    {
        $extractedPoints = [];
        $iterator = new PointIterator($points);

        foreach ($iterator as $point) {
            $extractedPoints[] = [
                'lat' => $point->lat,
                'lng' => $point->lng,
                'name' => $point->name
            ];
        }
        
        return $extractedPoints;
    }
}
