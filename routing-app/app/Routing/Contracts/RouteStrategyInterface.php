<?php

namespace App\Routing\Contracts;

interface RouteStrategyInterface
{
    public function calculateRoute(array $points): array;
}
