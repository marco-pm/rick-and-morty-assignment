<?php

use App\DTO\CharacterDTO;
use App\DTO\Factory\DtoFactory;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ApiServiceTest extends KernelTestCase
{
    public function characterDataProvider(): array
    {
        return [
            'character1' => [
                'data' => [
                    'id'       => 1,
                    'name'     => 'Character 1',
                    'status'   => 'Alive',
                    'species'  => 'Human',
                    'type'     => 'Type 1',
                    'gender'   => 'Male',
                    'origin'   => ['origin1', 'origin2'],
                    'location' => ['location1', 'location2'],
                    'image'    => 'https://example.com/image1.jpg',
                    'episode'  => ['episode1', 'episode2'],
                    'url'      => 'https://example.com/character/1',
                    'created'  => '2023-08-01T12:00:00+00:00',
                ],
            ],
            'character2' => [
                'data' => [
                    'id'       => 2,
                    'name'     => 'Character 2',
                    'status'   => 'Dead',
                    'species'  => 'Alien',
                    'type'     => 'Type 2',
                    'gender'   => 'Female',
                    'origin'   => ['origin3', 'origin4'],
                    'location' => ['location3', 'location4'],
                    'image'    => 'https://example.com/image2.jpg',
                    'episode'  => ['episode3', 'episode4'],
                    'url'      => 'https://example.com/character/2',
                    'created'  => '2023-08-02T12:00:00+00:00',
                ],
            ],
        ];
    }

    /**
     * @dataProvider characterDataProvider
     */
    public function testGetCharacterById(array $characterData): void
    {
        self::bootKernel();
        $dtoFactory = static::getContainer()->get(DtoFactory::class);

        $response = new MockResponse(json_encode($characterData), ['http_code' => 200]);
        $httpClient = new MockHttpClient([$response]);

        $apiService = new ApiService($httpClient, $dtoFactory, 'https://example.com/api/', 'characters', 'locations', 'episodes');

        $character = $apiService->getCharacterById($characterData['id']);

        $this->assertInstanceOf(CharacterDTO::class, $character);

        $this->assertEquals($characterData['id'], $character->id);
        $this->assertEquals($characterData['name'], $character->name);
        $this->assertEquals($characterData['status'], $character->status);
    }

    public function testGetCharacterByIdWithInvalidId(): void
    {
        self::bootKernel();
        $dtoFactory = static::getContainer()->get(DtoFactory::class);

        $response = new MockResponse('', ['http_code' => 404]);
        $httpClient = new MockHttpClient([$response]);
        $apiService = new ApiService($httpClient, $dtoFactory, 'https://example.com/api/', 'characters', 'locations', 'episodes');

        $character = $apiService->getCharacterById(1);

        $this->assertNull($character);
    }
}