<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\ApiServiceInterface;
use App\Service\CharacterSearchCriteria\Factory\CharacterSearchCriteriaFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class CharactersController extends AbstractController
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    /**
     * TODO: Add validation for category and handle ClassNotFoundException
     * @throws ClassNotFoundException
     */
    #[Route('/characters', name: 'characters', methods: ['GET'])]
    public function listCharacters(
        #[MapQueryParameter] string             $category,
        #[MapQueryParameter] string             $searchTerm,
        Request                                 $request,
        CharacterSearchCriteriaFactoryInterface $characterSearchCriteriaFactory,
        PaginatorInterface                      $paginator,
        int                                     $charactersPerPage,
    ): Response
    {
        $characters = [];
        $errorMessage = null;

        if ($searchTerm) {
            $searchCriteria = $characterSearchCriteriaFactory->create($category);
            try {
                $characters = $searchCriteria->search($searchTerm);
            } catch (ApiException $e) {
                $errorMessage = 'An error occurred. Please try again later.';
                $this->logger->error('An error occurred while searching for characters: ' . $e->getMessage(), [
                    'searchTerm' => $searchTerm,
                    'category'   => $category,
                    'exception'  => $e,
                ]);
            }
        }

        $paginatedCharacters = $paginator->paginate(
            $characters,
            $request->query->getInt('page', 1),
            $charactersPerPage
        );

        return $this->render('characters/list.html.twig', [
            'totalCharacters' => count($characters),
            'characters'      => $paginatedCharacters,
            'errorMessage'    => $errorMessage,
        ]);
    }

    #[Route('/character/{id<\d+>}', name: 'character', methods: 'GET')]
    public function showCharacter(int $id, Request $request, ApiServiceInterface $apiService): Response
    {
        $errorMessage = null;
        $character = null;

        try {
            $character = $apiService->getCharacterById($id);
        } catch (ApiException $e) {
            $errorMessage = 'An error occurred. Please try again later.';
            $this->logger->error('An error occurred while displaying character details: ' . $e->getMessage(), [
                'id'        => $id,
                'exception' => $e,
            ]);
        }

        $referer = $request->headers->get('referer');

        return $this->render('characters/details.html.twig', [
            'character'    => $character,
            'referer'      => $referer,
            'errorMessage' => $errorMessage,
        ]);
    }
}