<?php

namespace App\Controller\API;

use AllowDynamicProperties;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowDynamicProperties] class AuthorController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager,
                                ValidatorInterface $validator,
                                AuthorRepository $authorRepository)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
    }

    #[Route('/api/author', methods: ['GET'])]
    public function index(): Response
    {
        $authors = $this->authorRepository->findAll();
        return $this->json($authors, 200, [], ['groups' => ['author:read']]);
    }

    #[Route('/api/author/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        $author = $this->authorRepository->find($id);

        if (!$author) {
            return $this->json(['message' => 'Книга не найдена'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($author, 200, [], ['groups' => ['author:read']]);
    }

    #[Route('/api/author', methods: ['POST'])]
    public function create(Request $request): Response
    {
        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);

        $author = new Author();
        $author->setName($data['name']);
        $author->setSurname($data['surname']);

        $errors = $this->validator->validate($author);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        // Возвращаем созданную книгу в ответе
        return $this->json($author, Response::HTTP_CREATED, [], ['groups' => ['author:read']]); //Пустой массив это заголовки
    }


    #[Route('/api/author/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): Response
    {
        $author = $this->authorRepository->find($id);
        if (!$author) {
            return $this->json(['message' => 'Автор не найдена'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $author->setName($data['name']);
        $author->setSurname($data['surname']);

        $errors = $this->validator->validate($author);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($author, 200, [], ['groups' => ['author:write']]);
    }


    #[Route('/api/author/{id}', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $author = $this->authorRepository->find($id);
        if (!$author) {
            return $this->json(['message' => 'Автор не найден'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($author);
        $this->entityManager->flush();

        return $this->json(['message' => 'Автор удален']);
    }

}
