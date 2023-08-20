<?php

namespace App\Service\CharacterSearchCriteria;

class CharacterNameSearchCriteria implements CharacterSearchCriteriaInterface
{
    public function getCriteria(string $searchTerm): string
    {
        return 'getCharactersByName';
    }
}
