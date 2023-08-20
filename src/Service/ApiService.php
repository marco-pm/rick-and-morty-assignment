<?php

namespace App\Service;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\Factory\DtoFactory;
use App\DTO\LocationDTO;
use App\Exception\ApiException;
use App\Service\CharacterSearchCriteria\CharacterSearchCriteriaInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService implements ApiServiceInterface
{
    public function __construct(
        private HttpClientInterface $httpClient, // cachingHttpClient defined in services.yaml
        private DtoFactory          $characterDtoFactory,
        private readonly string     $apiBaseUrl,
        private readonly string     $characterEndpoint,
        private readonly string     $locationEndpoint,
        private readonly string     $episodeEndpoint,

    )
    {
    }

    /**
     * @throws ApiException
     */
    public function search(CharacterSearchCriteriaInterface $searchCriteria, string $searchTerm): array
    {
        $method = $searchCriteria->getCriteria($searchTerm);

        if (!method_exists($this, $method)) {
            throw new ApiException('Invalid search criteria.');
        }

        $results = $this->$method($searchTerm);

        // Check if $results contains LocationDTO or EpisodeDTO instances
        if ($results && ($results[0] instanceof LocationDTO || $results[0] instanceof EpisodeDTO)) {
            $urlsKey = $results[0] instanceof LocationDTO ? 'residents' : 'characters';

            $urls = array_reduce(
                $results,
                fn(array $carry, $dto) => array_merge($carry, $dto->$urlsKey),
                []
            );

            return $this->getCharactersFromEndpoints($urls);
        }

        return $results;
    }

    /**
     * @return CharacterDTO[]
     * @throws ApiException
     */
    protected function getCharactersFromEndpoints(array $characterEndpoints): array
    {
        $characterIds = array_map(fn($characterUrl) => $this->extractCharacterIdfromUrl($characterUrl), $characterEndpoints
        );

        return $this->getCharactersByIds($characterIds);
    }

    protected function extractCharacterIdfromUrl(string $characterUrl): int
    {
        $parts = explode('/', $characterUrl);
        return (int)end($parts);
    }

    /**
     * @return CharacterDTO[]
     * @throws ApiException
     */
    public function getCharactersByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $characterIdsString = implode(',', $ids);
        $apiUrl = $this->apiBaseUrl . $this->characterEndpoint . '/' . $characterIdsString;
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? array_map([$this->characterDtoFactory, 'createCharacter'], $data) : [];
    }

    /**
     * Runs a GET request to the REST API and returns the response data.
     *
     * @throws ApiException
     */
    private function getResponseData(string $apiUrl): array
    {
        $allData = [];

        try {
            $response = $this->httpClient->request('GET', $apiUrl);

            // deal with error status codes
            $statusCode = $response->getStatusCode(); // prevents HttpException from being thrown
            if ($statusCode !== Response::HTTP_OK) {
                if ($statusCode === Response::HTTP_NOT_FOUND) {
                    return []; // Return empty array for 404
                }
                throw new ApiException('API request failed with status code: ' . $statusCode);
            }

            $data = $response->toArray();
            if (!isset($data['results'])) {
                // No pagination
                return $data;
            }

            // Fetch the additional pages in parallel
            $allData = $data['results'];
            if (!empty($data['info']['pages'])) {
                $totalPages = $data['info']['pages'];
                $responses = [];

                for ($i = 2; $i <= $totalPages; $i++) {
                    $uri = $apiUrl . (str_contains($apiUrl, '?') ? '&' : '?') . 'page=' . $i;
                    $responses[] = $this->httpClient->request('GET', $uri);
                }

                foreach ($responses as $response) {
                    // deal with error status codes
                    $statusCode = $response->getStatusCode(); // prevents HttpException from being thrown
                    if ($statusCode !== Response::HTTP_OK && $statusCode !== Response::HTTP_NOT_FOUND) {
                        throw new ApiException('API request failed with status code: ' . $statusCode);
                    }

                    $data = $response->toArray();
                    $allData = array_merge($allData, $data['results']);
                }
            }
        } catch (
        TransportExceptionInterface|DecodingExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
        $e
        ) {
            throw new ApiException('Error fetching data from API: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $allData;
    }

    /**
     * @throws ApiException
     */
    public function getCharacterById(int $id): ?CharacterDTO
    {
        $apiUrl = $this->apiBaseUrl . $this->characterEndpoint . '/' . $id;
        $data = $this->getResponseData($apiUrl);

        if ($data) {
            if (!empty($data['location']['url'])) {
                $location = $this->getLocationByUrl($data['location']['url']);
                if ($location) {
                    $data['dimension'] = $location->dimension ?? null;
                }
            }

            return $this->characterDtoFactory->createCharacter($data);
        }

        return null;
    }

    /**
     * @throws ApiException
     */
    public function getLocationByUrl(string $url): ?LocationDTO
    {
        $data = $this->getResponseData($url);

        return !empty($data) ? $this->characterDtoFactory->createLocation($data) : null;
    }

    /**
     * @return CharacterDTO[]|null
     * @throws ApiException
     */
    public function getCharactersByName(string $name): ?array
    {
        $apiUrl = $this->apiBaseUrl . $this->characterEndpoint . '/?name=' . urlencode($name);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? array_map([$this->characterDtoFactory, 'createCharacter'], $data) : [];
    }

    /**
     * @return LocationDTO[]
     * @throws ApiException
     */
    public function getLocationsByName(string $name): array
    {
        $apiUrl = $this->apiBaseUrl . $this->locationEndpoint . '/?name=' . urlencode($name);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? array_map([$this->characterDtoFactory, 'createLocation'], $data) : [];
    }

    /**
     * @return LocationDTO[]
     * @throws ApiException
     */
    public function getLocationsByDimension(string $dimension): array
    {
        $apiUrl = $this->apiBaseUrl . $this->locationEndpoint . '/?dimension=' . urlencode($dimension);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? array_map([$this->characterDtoFactory, 'createLocation'], $data) : [];
    }

    /**
     * @return EpisodeDTO[]|null
     * @throws ApiException
     */
    public function getEpisodesByName(string $name): ?array
    {
        $apiUrl = $this->apiBaseUrl . $this->episodeEndpoint . '/?name=' . urlencode($name);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? array_map([$this->characterDtoFactory, 'createEpisode'], $data) : [];
    }

    /**
     * @return EpisodeDTO[]|null
     * @throws ApiException
     */
    public function getEpisodesByCode(string $code): ?array
    {
        $apiUrl = $this->apiBaseUrl . $this->episodeEndpoint . '/?episode=' . urlencode($code);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? array_map([$this->characterDtoFactory, 'createEpisode'], $data) : [];
    }
}