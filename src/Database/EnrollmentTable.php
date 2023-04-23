<?php

namespace App\Database;

use App\Model\Domain\Enrollment;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class EnrollmentTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(
        Connection $connection,
        EntityManagerInterface $entityManager,
    ) {
        $this->connection = $connection;
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

    /**
     * @param string $courseId
     * @return void
     * @throws Exception
     */
    public function deleteCourseEnrollments(string $courseId): void
    {
        $query = <<<SQL
            UPDATE course_enrollment
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE
                course_id = ?
            SQL;
        $params = [$courseId];
        $this->connection->executeQuery($query, $params);
    }
}
