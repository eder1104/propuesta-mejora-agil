<?php

namespace App\Routing;

use App\Routing\Contracts\RouteStrategyInterface;

class RoutingService
{
    public function __construct(private RouteStrategyInterface $strategy) {}

    public function executeRouting(array $points): array
    {
        return $this->strategy->calculateRoute($points);
    }
}
