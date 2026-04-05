<?php

namespace App\Services\Routing;

interface RouteStrategyInterface
{
    public function calculateRoute(array $points): array;
}
