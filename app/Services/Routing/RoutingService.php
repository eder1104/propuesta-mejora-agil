<?php

namespace App\Services\Routing;

class RoutingService
{
    public function __construct(private RouteStrategyInterface $strategy) {}

    public function executeRouting(array $points): array
    {
        $iterator = new PointIterator($points);
        $extractedPoints = [];

        foreach ($iterator as $point) {
            $extractedPoints[] = $point;
        }

        return $this->strategy->calculateRoute($extractedPoints);
    }
}
