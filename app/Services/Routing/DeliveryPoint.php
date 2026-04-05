<?php

namespace App\Services\Routing;

class DeliveryPoint
{
    public function __construct(
        public readonly float $lat,
        public readonly float $lng,
        public readonly string $name,
    ) {}
}
