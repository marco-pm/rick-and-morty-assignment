<?php

namespace App\Service;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\LocationDTO;
use App\Exception\ApiException;
use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;

interface ApiServiceInterface
{
    /**
     * @throws ApiException
     */
    public function search(CharacterSearchCriteriaInterface $searchCriteria, string $searchTerm): array;

    /**
     * @throws ApiException
     */
    public function getCharacterById(int $id): ?CharacterDTO;

    /**
     * @return CharacterDTO[]|null
     *
     * @throws ApiException
     */
    public function getCharactersByName(string $name): ?array;

    /**
     * @return CharacterDTO[]
     *
     * @throws ApiException
     */
    public function getCharactersByIds(array $ids): array;

    /**
     * @throws ApiException
     */
    public function getLocationByUrl(string $url): ?LocationDTO;

    /**
     * @return LocationDTO[]
     *
     * @throws ApiException
     */
    public function getLocationsByName(string $name): array;

    /**
     * @return LocationDTO[]
     *
     * @throws ApiException
     */
    public function getLocationsByDimension(string $dimension): array;

    /**
     * @return EpisodeDTO[]|null
     *
     * @throws ApiException
     */
    public function getEpisodesByName(string $name): ?array;

    /**
     * @return EpisodeDTO[]|null
     *
     * @throws ApiException
     */
    public function getEpisodesByCode(string $code): ?array;
}
