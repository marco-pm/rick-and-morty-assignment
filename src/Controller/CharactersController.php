<?php

namespace App\Controller;

use App\Service\ApiServiceInterface;
use App\Service\CharacterSearchCriteria\Factory\CharacterSearchCriteriaFactoryInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class CharactersController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[Route('/characters', name: 'characters', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] string             $category,
        #[MapQueryParameter] string             $searchTerm,
        Request                                 $request,
        CharacterSearchCriteriaFactoryInterface $characterSearchCriteriaFactory,
        PaginatorInterface                      $paginator,
        int                                     $charactersPerPage
    ): Response
    {
        $characters = [];
        $errorMessage = null;

        if ($searchTerm) {
            $searchCriteria = $characterSearchCriteriaFactory->create($category);
            try {
                $characters = $searchCriteria->search($searchTerm);
            } catch (Exception $e) {
                // TODO
                /*if ($this->getParameter('kernel.environment') === 'dev') {
                    $errorMessage = $e->getMessage();
                } else {
                    $errorMessage = 'An error occurred. Please try again later.';
                }*/
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
    public function show(int $id, Request $request, ApiServiceInterface $apiService): Response
    {
        $errorMessage = null;
        $character = null;

        try {
            $character = $apiService->getCharacterById($id);
        } catch (Exception $e) {
            /*if ($this->getParameter('kernel.environment') === 'dev') {
                $errorMessage = $e->getMessage();
            } else {
                $errorMessage = 'An error occurred. Please try again later.';
            }*/
        }

        $referer = $request->headers->get('referer');

        return $this->render('characters/details.html.twig', [
            'character'    => $character,
            'referer'      => $referer,
            'errorMessage' => $errorMessage,
        ]);
    }
}