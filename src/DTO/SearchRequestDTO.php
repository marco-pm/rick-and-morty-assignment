<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class SearchRequestDTO
{
    public function __construct(
        #[Assert\Choice(
            choices: ['name', 'location', 'dimension', 'episode-name', 'episode-code'],
            message: 'Invalid search category.'
        )]
        public string $category,

        #[Assert\NotBlank]
        public string $searchTerm
    )
    {
    }
}
