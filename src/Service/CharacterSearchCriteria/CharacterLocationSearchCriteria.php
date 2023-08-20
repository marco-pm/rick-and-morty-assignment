<?php

namespace App\Service\CharacterSearchCriteria;

class CharacterLocationSearchCriteria implements CharacterSearchCriteriaInterface
{
    public function getCriteria(string $searchTerm): string
    {
        return 'getLocationsByName';
    }
}