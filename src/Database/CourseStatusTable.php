<?php

namespace App\Database;

use App\Model\Domain\CourseStatus;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseStatusTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CourseStatusTable::class);
    }

    public function add(CourseStatus $courseStatus): void
    {
        $this->entityManager->persist($courseStatus);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
