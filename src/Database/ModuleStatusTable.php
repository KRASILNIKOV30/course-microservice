<?php

namespace App\Database;

use App\Model\Domain\ModuleStatus;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ModuleStatusTable
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ModuleStatus::class);
    }

    public function findOne(string $moduleId, string $enrollmentId): ?ModuleStatus
    {
        return $this->repository->findOneBy(['module_id' => $moduleId, 'enrollment_id' => $enrollmentId]);
    }

    public function add(ModuleStatus $moduleStatus): void
    {
        $this->entityManager->persist($moduleStatus);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
