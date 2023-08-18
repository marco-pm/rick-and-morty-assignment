<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\Exception\ApiException;

class CharacterEpisodeCodeSearchCriteria extends CharacterSearchCriteria
{
    /**
     * @return CharacterDTO[]
     * @throws ApiException
     */
    public function search(string $searchTerm): array
    {
        $episodes = $this->apiService->getEpisodesByCode($searchTerm);

        $characterUrls = array_reduce(
            $episodes,
            fn(array $carry, EpisodeDTO $episode) => array_merge($carry, $episode->characters),
            []
        );

        return $this->getCharactersFromEndpoints($characterUrls);
    }
}