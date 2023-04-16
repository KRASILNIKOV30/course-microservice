<?php

namespace App\Database;

use App\Model\Domain\ModuleStatus;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ModuleStatusTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ModuleStatus::class);
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
