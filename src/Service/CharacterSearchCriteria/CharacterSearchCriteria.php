<?php

namespace App\Service\CharacterSearchCriteria;

use App\DTO\CharacterDTO;
use App\Exception\ApiException;
use App\Service\ApiServiceInterface;

abstract class CharacterSearchCriteria implements CharacterSearchCriteriaInterface
{
    public function __construct(
        protected ApiServiceInterface $apiService
    )
    {
    }

    /**
     * @return CharacterDTO[]
     * @throws ApiException
     */
    protected function getCharactersFromEndpoints(array $characterEndpoints): array
    {
        $characterIds = array_map(fn($characterUrl) => $this->extractCharacterIdfromUrl($characterUrl), $characterEndpoints
        );

        return $this->apiService->getCharactersByIds($characterIds);
    }

    protected function extractCharacterIdfromUrl(string $characterUrl): int
    {
        $parts = explode('/', $characterUrl);
        return (int)end($parts);
    }
}