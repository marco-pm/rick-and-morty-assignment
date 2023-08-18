<?php

namespace App\Service;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\LocationDTO;
use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

        // TODO: CachingHttpClient defeats concurrency https://github.com/symfony/symfony/issues/36967
        // so, if we use the CachingHttpClient, the requests will be sequential.
        // consider a "manual" approach, eg https://developer.happyr.com/http-client-and-caching

        /*$store = new Store(self::CACHE_PATH);
        $this->httpClient = new CachingHttpClient($this->httpClient, $store, ['default_ttl' => self::CACHE_TTL]);*/
    }

    /**
     * @throws ApiException
     */
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
     * TODO: consume the graphql API instead of the REST API
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