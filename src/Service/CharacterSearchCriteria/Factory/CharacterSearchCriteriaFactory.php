<?php

namespace App\Service\CharacterSearchCriteria\Factory;

use App\Service\ApiServiceInterface;
use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class CharacterSearchCriteriaFactory implements CharacterSearchCriteriaFactoryInterface
{
    public function __construct(
        protected ApiServiceInterface $apiService
    )
    {
    }

    public function create(string $searchCriteria): CharacterSearchCriteriaInterface
    {
        $searchCriteriaUc = str_replace(' ', '', ucwords(str_replace('-', ' ', $searchCriteria)));

        $characterSearchCriteriaClassBasename = 'Character' . $searchCriteriaUc . 'SearchCriteria';

        $characterSearchCriteria = self::CHARACTER_SEARCH_CRITERIA_NAMESPACE . $characterSearchCriteriaClassBasename;

        if (!class_exists($characterSearchCriteria)) {
            throw new ClassNotFoundException($characterSearchCriteria);
        }

        return new $characterSearchCriteria($this->apiService);
    }
}