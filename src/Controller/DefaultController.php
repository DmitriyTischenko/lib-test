<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(BookRepository $bookRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $page = $request->query->getInt('page', 1);

        $query = $bookRepository->findAllBooksWithAuthorsQuery();
        $books = $paginator->paginate(
            $query,
            $page,
            10
        );

        return $this->render('default/index.html.twig', [
            'books' => $books,
        ]);
    }
}
