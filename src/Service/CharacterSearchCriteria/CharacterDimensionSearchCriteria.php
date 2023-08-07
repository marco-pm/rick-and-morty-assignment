<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;
use App\DTO\LocationDTO;

class CharacterDimensionSearchCriteria extends CharacterSearchCriteria
{
    /**
     * @return CharacterDTO[]
     */
    public function search(string $searchTerm): array
    {
        $locations = $this->apiService->getLocationsByDimension($searchTerm);

        $residentUrls = array_reduce(
            $locations,
            fn(array $carry, LocationDTO $location) => array_merge($carry, $location->residents),
            []
        );

        return $this->getCharactersFromEndpoints($residentUrls);
    }
}