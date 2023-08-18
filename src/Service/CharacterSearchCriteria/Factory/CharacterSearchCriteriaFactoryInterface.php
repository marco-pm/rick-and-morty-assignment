<?php

namespace App\Service\CharacterSearchCriteria\Factory;

use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

interface CharacterSearchCriteriaFactoryInterface
{
    public const CHARACTER_SEARCH_CRITERIA_NAMESPACE = "App\Service\CharacterSearchCriteria\\";

    /**
     * @throws ClassNotFoundException
     */
    public function create(string $searchCriteria): CharacterSearchCriteriaInterface;
}