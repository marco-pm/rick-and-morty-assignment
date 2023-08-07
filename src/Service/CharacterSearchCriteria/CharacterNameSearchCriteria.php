<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;

class CharacterNameSearchCriteria extends CharacterSearchCriteria
{
    /**
     * @return CharacterDTO[]
     */
    public function search(string $searchTerm): array
    {
        return $this->apiService->getCharactersByName($searchTerm);
    }
}