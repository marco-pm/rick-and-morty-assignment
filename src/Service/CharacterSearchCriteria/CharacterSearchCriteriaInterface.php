<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;

interface CharacterSearchCriteriaInterface
{
    /**
     * @return CharacterDTO[]
     */
    public function search(string $searchTerm): array;
}