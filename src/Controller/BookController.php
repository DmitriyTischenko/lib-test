<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowDynamicProperties]
class BookController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager,
                                BookRepository $bookRepository,
                                ValidatorInterface $validator,
                                AuthorRepository $authorRepository)
    {
        $this->entityManager = $entityManager;
        $this->bookRepository = $bookRepository;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
    }

    #[Route('/api/books', methods: ['GET'])]
    public function index(): Response
    {
        $books = $this->bookRepository->findAll();
        return $this->json($books);
    }

    #[Route('/api/books/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($book);
    }

    #[Route('/api/books', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $book = new Book();
        $book->setTitle($data['title']);
        $book->setDescription($data['description']);
        $author = $this->authorRepository->find($data['author_id']);

        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        // Установка автора
        $book->setAuthor($author);

        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Сохранение через EntityManager
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json($book, Response::HTTP_CREATED);
    }

    #[Route('/api/books/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $book->setTitle($data['title']);
        $book->setDescription($data['description']);

        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Обновление через EntityManager
        $this->entityManager->flush();

        return $this->json($book);
    }

    #[Route('/api/books/{id}', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }

        // Удаление через EntityManager
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return $this->json(['message' => 'Книга удалена']);
    }
}
