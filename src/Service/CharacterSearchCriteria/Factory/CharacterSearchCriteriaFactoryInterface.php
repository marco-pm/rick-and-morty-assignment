<?php

namespace App\Service\CharacterSearchCriteria\Factory;

use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

interface CharacterSearchCriteriaFactoryInterface
{
    /**
     * @throws ClassNotFoundException
     */
    public function create(string $searchCriteria): CharacterSearchCriteriaInterface;
}
