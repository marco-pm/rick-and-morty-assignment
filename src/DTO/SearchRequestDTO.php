<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SearchRequestDTO
{
    public function __construct(
        #[Assert\Choice(
            choices: ['name', 'location', 'dimension', 'episode-name', 'episode-code'],
            message: 'Invalid search category.'
        )]
        public readonly string $category,

        #[Assert\NotBlank]
        public readonly string $searchTerm
    )
    {
    }
}