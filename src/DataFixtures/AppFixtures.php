<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // Создание авторов
        for ($i = 0; $i < 10; $i++) {
            $author = new Author();
            $author->setName('Name ' .$i);
            $author->setSurname('Surname ' .$i);

            $manager->persist($author);

            // Создание книг, привязанных к авторам
            for ($j = 0; $j < 3; $j++) {
                $book = new Book();
                $book->setTitle('Title ' .$i);
                $book->setDescription('Description ' .$i);
                $book->setAuthor($author); // Привязываем автора к книге

                $manager->persist($book);
            }
        }

        $manager->flush();
    }
}
