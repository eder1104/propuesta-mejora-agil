<?php

namespace App\Routing;

use Iterator;

class PointIterator implements Iterator
{
    private int $position = 0;

    public function __construct(private array $points)
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->points[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->points[$this->position]);
    }
}
