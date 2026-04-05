<?php

namespace App\Services\Routing;

class OSRMFastRouteStrategy implements RouteStrategyInterface
{
    public function calculateRoute(array $points): array
    {
        $preparedPoints = [];
        
        foreach ($points as $point) {
            $preparedPoints[] = [
                'lat' => $point->lat,
                'lng' => $point->lng,
                'name' => $point->name
            ];
        }
        
        return $preparedPoints;
    }
}
