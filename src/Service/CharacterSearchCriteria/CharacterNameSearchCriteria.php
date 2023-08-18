<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;
use App\Exception\ApiException;

class CharacterNameSearchCriteria extends CharacterSearchCriteria
{
    /**
     * @return CharacterDTO[]
     * @throws ApiException
     */
    public function search(string $searchTerm): array
    {
        return $this->apiService->getCharactersByName($searchTerm);
    }
}