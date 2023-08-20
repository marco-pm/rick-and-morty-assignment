<?php

namespace App\Service\CharacterSearchCriteria;

class CharacterEpisodeNameSearchCriteria implements CharacterSearchCriteriaInterface
{
    public function getCriteria(string $searchTerm): string
    {
        return 'getEpisodesByName';
    }
}
