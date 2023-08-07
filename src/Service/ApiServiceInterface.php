<?php

namespace App\Service;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\LocationDTO;

interface ApiServiceInterface
{
    public function getCharacterById(int $id): ?CharacterDTO;

    /**
     * @return CharacterDTO[]|null
     */
    public function getCharactersByName(string $name): ?array;

    /**
     * @return CharacterDTO[]
     */
    public function getCharactersByIds(array $ids): array;

    public function getLocationByUrl(string $url): ?LocationDTO;

    /**
     * @return LocationDTO[]
     */
    public function getLocationsByName(string $name): array;

    /**
     * @return LocationDTO[]
     */
    public function getLocationsByDimension(string $dimension): array;

    /**
     * @return EpisodeDTO[]|null
     */
    public function getEpisodesByName(string $name): ?array;

    /**
     * @return EpisodeDTO[]|null
     */
    public function getEpisodesByCode(string $code): ?array;
}