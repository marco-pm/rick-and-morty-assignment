<?php

namespace App\DTO\Factory;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\LocationDTO;
use Symfony\Component\Serializer\SerializerInterface;

class DtoFactory implements DtoFactoryInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function createCharacter(array $data): CharacterDTO
    {
        return $this->serializer->denormalize($data, CharacterDTO::class);
    }

    public function createEpisode(array $data): EpisodeDTO
    {
        return $this->serializer->denormalize($data, EpisodeDTO::class);
    }

    public function createLocation(array $data): LocationDTO
    {
        return $this->serializer->denormalize($data, LocationDTO::class);
    }

}