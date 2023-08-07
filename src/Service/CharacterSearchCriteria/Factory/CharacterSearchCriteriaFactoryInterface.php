<?php

namespace App\Service\CharacterSearchCriteria\Factory;

use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;

interface CharacterSearchCriteriaFactoryInterface
{
    public const CHARACTER_SEARCH_CRITERIA_NAMESPACE = "App\Service\CharacterSearchCriteria\\";

    public function create(string $searchCriteria): CharacterSearchCriteriaInterface;
}