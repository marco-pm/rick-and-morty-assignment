<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;
use App\DTO\LocationDTO;
use App\Exception\ApiException;

class CharacterLocationSearchCriteria extends CharacterSearchCriteria
{
    /**
     * @return CharacterDTO[]
     * @throws ApiException
     */
    public function search(string $searchTerm): array
    {
        $locations = $this->apiService->getLocationsByName($searchTerm);

        $residentUrls = array_reduce(
            $locations,
            fn(array $carry, LocationDTO $location) => array_merge($carry, $location->residents),
            []
        );

        return $this->getCharactersFromEndpoints($residentUrls);
    }
}