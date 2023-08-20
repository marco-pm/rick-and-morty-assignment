<?php

namespace App\DTO;

use DateTimeImmutable;

readonly class EpisodeDTO
{
    public function __construct(
        public int               $id,
        public string            $name,
        public string            $air_date,
        public string            $episode,
        public array             $characters,
        public string            $url,
        public DateTimeImmutable $created,
    )
    {
    }
}