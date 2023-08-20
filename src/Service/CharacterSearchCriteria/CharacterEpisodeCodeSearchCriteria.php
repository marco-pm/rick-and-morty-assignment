<?php

namespace App\Service\CharacterSearchCriteria;

class CharacterEpisodeCodeSearchCriteria implements CharacterSearchCriteriaInterface
{
    public function getCriteria(string $searchTerm): string
    {
        return 'getEpisodesByCode';
    }
}
