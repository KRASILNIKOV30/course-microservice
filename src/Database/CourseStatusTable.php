<?php

namespace App\Database;

use App\Model\Domain\CourseStatus;
use Doctrine\ORM\EntityManagerInterface;

class CourseStatusTable
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
