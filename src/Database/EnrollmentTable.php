<?php

namespace App\Database;

use App\Model\Domain\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class EnrollmentTable
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager,)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Enrollment::class);
    }

    public function add(Enrollment $enrollment): void
    {
        $this->entityManager->persist($enrollment);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function findOne(string $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param string $courseId
     * @return Enrollment[]
     */
    public function findAll(string $courseId): array
    {
        return $this->repository->findBy(["course_id" => $courseId]);
    }
}
