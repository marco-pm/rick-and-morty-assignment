<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;

class CharacterEpisodeNameSearchCriteria extends CharacterSearchCriteria
{
    /**
     * @return CharacterDTO[]
     */
    public function search(string $searchTerm): array
    {
        $episodes = $this->apiService->getEpisodesByName($searchTerm);

        $characterUrls = array_reduce(
            $episodes,
            fn(array $carry, EpisodeDTO $episode) => array_merge($carry, $episode->characters),
            []
        );

        return $this->getCharactersFromEndpoints($characterUrls);
    }
}