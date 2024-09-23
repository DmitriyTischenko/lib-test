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
                                BookRepository         $bookRepository,
                                ValidatorInterface     $validator,
                                AuthorRepository       $authorRepository)
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
        return $this->json($books, 200, [], ['groups' => ['book:read']]);
    }

    #[Route('/api/books/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($book, 200, [], ['groups' => ['book:read']]);
    }

    #[Route('/api/books', methods: ['POST'])]
    public function create(Request $request): Response
    {
        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);

        // Создаем новую книгу
        $book = new Book();
        $book->setTitle($data['title']);
        $book->setDescription($data['description']);

        // Ищем автора по его id
        if (isset($data['author'])) {
            // Ищем автора по его ID
            $author = $this->authorRepository->find($data['author']);
            if (!$author) {
                return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
            }
            // Устанавливаем автора для книги
            $book->setAuthor($author);
        } else {
            // Если автор не указан, устанавливаем значение null
            $book->setAuthor(null);
        }

        // Валидация книги
        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Сохраняем книгу в базе данных
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // Возвращаем созданную книгу в ответе
        return $this->json($book, Response::HTTP_CREATED, [], ['groups' => ['book:read']]); //Пустой массив это заголовки
    }


    #[Route('/api/books/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        // Находим книгу по ID
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }

        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);

        // Обновляем поля книги
        $book->setTitle($data['title']);
        $book->setDescription($data['description']);

        // Проверяем, указан ли author_id, и если да, проверяем наличие автора
        if (isset($data['author']) && $data['author'] !== null) {
            // Ищем автора по его ID
            $author = $this->authorRepository->find($data['author']);
            if (!$author) {
                // Возвращаем ошибку, если автор не найден
                return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
            }
            // Устанавливаем автора для книги, если он найден
            $book->setAuthor($author);
        }

        // Валидация книги
        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        // Сохраняем изменения
        $this->entityManager->flush();

        // Возвращаем обновленную книгу
        return $this->json($book, Response::HTTP_OK, [], ['groups' => ['book:read']]);
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
