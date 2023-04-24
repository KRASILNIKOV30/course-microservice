<?php

namespace App\Database;

use App\Model\Domain\Course;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Course::class);
    }

    public function findOne(string $id): ?Course
    {
        return $this->repository->find($id);
    }

    public function add(Course $course): void
    {
        $this->entityManager->persist($course);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
