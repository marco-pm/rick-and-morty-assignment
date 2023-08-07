<?php

namespace App\DTO;

use DateTimeImmutable;

class EpisodeDTO
{
    public function __construct(
        public readonly int               $id,
        public readonly string            $name,
        public readonly string            $air_date,
        public readonly string            $episode,
        public readonly array             $characters,
        public readonly string            $url,
        public readonly DateTimeImmutable $created,
    )
    {
    }
}