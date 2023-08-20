<?php

namespace App\DTO;

use DateTimeImmutable;

readonly class CharacterDTO
{
    public function __construct(
        public int               $id,
        public string            $name,
        public string            $status,
        public string            $species,
        public ?string           $type,
        public string            $gender,
        public array             $origin,
        public array             $location,
        public string            $image,
        public array             $episode,
        public string            $url,
        public DateTimeImmutable $created,
        public ?string           $dimension = null,
    )
    {
    }

    public function getFormattedCreationDate(): string
    {
        return $this->created->format('F j, Y \a\t H:i:s');
    }
}
