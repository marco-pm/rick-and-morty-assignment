<?php

namespace App\DTO;

readonly class LocationDTO
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $type,
        public string $dimension,
        public array  $residents,
        public string $url,
        public string $created,
    )
    {
    }
}
