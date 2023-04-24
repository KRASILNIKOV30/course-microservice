<?php

namespace App\Database;

use App\Model\Domain\CourseStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseStatusRepository
{
    private EntityRepository $repository;
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CourseStatus::class);
    }

    public function findOne(string $enrollmentId): ?CourseStatus
    {
        return $this->repository->find($enrollmentId);
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
