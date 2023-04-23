<?php

namespace App\Database;

use App\Model\Domain\Module;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseModuleTable
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Module::class);
    }

    public function findOne(string $id): ?Module
    {
        return $this->repository->find($id);
    }

    public function add(Module $module): void
    {
        $this->entityManager->persist($module);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
