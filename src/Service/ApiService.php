<?php

namespace App\Service;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\LocationDTO;
use App\Exception\ApiException;
use Exception;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService implements ApiServiceInterface
{
    private const CACHE_PATH = __DIR__ . '/../../var/cache/api';

    private const CACHE_TTL = 3600;

    private SerializerInterface $serializer;

    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly string     $apiBaseUrl,
        private readonly string     $characterEndpoint,
        private readonly string     $locationEndpoint,
        private readonly string     $episodeEndpoint,
    )
    {
        $this->serializer = new Serializer([new ArrayDenormalizer(), new ObjectNormalizer()]);

        $store = new Store(self::CACHE_PATH);
        $this->httpClient = new CachingHttpClient($this->httpClient, $store, ['default_ttl' => self::CACHE_TTL]);
    }

    public function getCharacterById(int $id): ?CharacterDTO
    {
        $apiUrl = $this->apiBaseUrl . $this->characterEndpoint . '/' . $id;
        $data = $this->getResponseData($apiUrl);

        if ($data) {
            $character = $this->serializer->denormalize($data, CharacterDTO::class);

            // Fetch last location and add dimension to the returned object
            if (!empty($data['location']['url'])) {
                $location = $this->getLocationByUrl($data['location']['url']);
                $character->dimension = $location?->dimension;
            }

            return $character;
        }

        return null;
    }

    /**
     * Runs a GET request to the API and returns the response data.
     * TODO: handle other status codes and exceptions
     *
     * @throws ApiException
     */
    private function getResponseData(string $apiUrl): array
    {
        $allData = [];

        try {
            do {
                $response = $this->httpClient->request('GET', $apiUrl);

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $data = $response->toArray();

                    if (!isset($data['results'])) {
                        // No pagination
                        return $data;
                    }

                    $allData = array_merge($allData, $data['results']);

                    // If there are more pages, fetch them
                    $nextPageLink = $data['info']['next'] ?? null;
                    if ($nextPageLink) {
                        // Update the API URL to fetch the next page
                        $apiUrl = $nextPageLink;
                    } else {
                        break;
                    }
                } else {
                    if ($response->getStatusCode() !== Response::HTTP_NOT_FOUND) {
                        throw new ApiException('API request failed with status code: ' . $response->getStatusCode());
                    }
                }
            } while ($response->getStatusCode() === Response::HTTP_OK);
        } catch (Exception $e) {
            throw new ApiException('Error fetching data from API: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $allData;
    }

    /**
     * @throws ApiException
     */
    public function getLocationByUrl(string $url): ?LocationDTO
    {
        $data = $this->getResponseData($url);

        return !empty($data) ? $this->serializer->denormalize($data, LocationDTO::class) : null;
    }

    /**
     * @return CharacterDTO[]|null
     * @throws ApiException
     */
    public function getCharactersByName(string $name): ?array
    {
        $apiUrl = $this->apiBaseUrl . $this->characterEndpoint . '/?name=' . urlencode($name);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? $this->serializer->denormalize($data, CharacterDTO::class . '[]') : [];
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

        return !empty($data) ? $this->serializer->denormalize($data, CharacterDTO::class . '[]') : [];
    }

    /**
     * @return LocationDTO[]
     * @throws ApiException
     */
    public function getLocationsByName(string $name): array
    {
        $apiUrl = $this->apiBaseUrl . $this->locationEndpoint . '/?name=' . urlencode($name);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? $this->serializer->denormalize($data, LocationDTO::class . '[]') : [];
    }

    /**
     * @return LocationDTO[]
     * @throws ApiException
     */
    public function getLocationsByDimension(string $dimension): array
    {
        $apiUrl = $this->apiBaseUrl . $this->locationEndpoint . '/?dimension=' . urlencode($dimension);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? $this->serializer->denormalize($data, LocationDTO::class . '[]') : [];
    }

    /**
     * @return EpisodeDTO[]|null
     * @throws ApiException
     */
    public function getEpisodesByName(string $name): ?array
    {
        $apiUrl = $this->apiBaseUrl . $this->episodeEndpoint . '/?name=' . urlencode($name);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? $this->serializer->denormalize($data, EpisodeDTO::class . '[]') : [];
    }

    /**
     * @return EpisodeDTO[]|null
     * @throws ApiException
     */
    public function getEpisodesByCode(string $code): ?array
    {
        $apiUrl = $this->apiBaseUrl . $this->episodeEndpoint . '/?episode=' . urlencode($code);
        $data = $this->getResponseData($apiUrl);

        return !empty($data) ? $this->serializer->denormalize($data, EpisodeDTO::class . '[]') : [];
    }
}