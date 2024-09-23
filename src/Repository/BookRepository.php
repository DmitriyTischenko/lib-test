<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $_em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Book::class);
    }
    public function save(Book $book): void
    {
        $this->_em->persist($book);
        $this->_em->flush();
    }

    public function remove(Book $book): void
    {
        $this->_em->remove($book);
        $this->_em->flush();
    }

    public function findAllBooksWithAuthorsQuery()
    {
        return $this->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->addSelect('a')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
