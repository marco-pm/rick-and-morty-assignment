<?php

namespace App\DTO\Factory;

use App\DTO\CharacterDTO;
use App\DTO\EpisodeDTO;
use App\DTO\LocationDTO;

interface DtoFactoryInterface
{
    public function createCharacter(array $data): CharacterDTO;

    public function createEpisode(array $data): EpisodeDTO;

    public function createLocation(array $data): LocationDTO;
}
