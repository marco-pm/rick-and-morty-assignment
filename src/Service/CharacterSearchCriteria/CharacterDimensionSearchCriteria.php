<?php

namespace App\Service\CharacterSearchCriteria;

class CharacterDimensionSearchCriteria implements CharacterSearchCriteriaInterface
{
    public function getCriteria(string $searchTerm): string
    {
        return 'getCharactersByDimension';
    }
}