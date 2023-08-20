<?php

namespace App\Service\CharacterSearchCriteria\Factory;

use App\Service\ApiServiceInterface;
use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class CharacterSearchCriteriaFactory implements CharacterSearchCriteriaFactoryInterface
{
    public function __construct(
        private iterable              $characterSearchCriteria,
        protected ApiServiceInterface $apiService
    )
    {
    }

    public function create(string $searchCriteria): CharacterSearchCriteriaInterface
    {
        $searchCriteriaUc = str_replace(' ', '', ucwords(str_replace('-', ' ', $searchCriteria)));
        $characterSearchCriteriaClassBasename = 'Character' . $searchCriteriaUc . 'SearchCriteria';

        foreach ($this->characterSearchCriteria as $characterSearchCriteria) {
            if (str_ends_with(get_class($characterSearchCriteria), $characterSearchCriteriaClassBasename)) {
                return $characterSearchCriteria;
            }
        }

        throw new ClassNotFoundException(sprintf('Character search criteria %s not found', $searchCriteria));
    }
}
