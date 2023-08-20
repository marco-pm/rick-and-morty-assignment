<?php

namespace App\Service\CharacterSearchCriteria;

interface CharacterSearchCriteriaInterface
{
    public function getCriteria(string $searchTerm): string;
}