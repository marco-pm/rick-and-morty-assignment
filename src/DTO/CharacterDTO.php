<?php

namespace App\DTO;

use DateTimeImmutable;

class CharacterDTO
{
    public function __construct(
        public readonly int               $id,
        public readonly string            $name,
        public readonly string            $status,
        public readonly string            $species,
        public readonly ?string           $type,
        public readonly string            $gender,
        public readonly array             $origin,
        public readonly array             $location,
        public readonly string            $image,
        public readonly array             $episode,
        public readonly string            $url,
        public readonly DateTimeImmutable $created,
        public ?string                    $dimension = null,
    )
    {
    }

    public function getFormattedCreationDate(): string
    {
        return $this->created->format('F j, Y \a\t H:i:s');
    }
}