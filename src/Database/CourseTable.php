<?php

namespace App\Database;

use App\Model\Domain\Module;
use Doctrine\DBAL\Connection;
use App\Model\Domain\Course;
use App\Model\Data\CourseStatusData;
use App\Model\Data\ModuleStatusData;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->connection = $connection;
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

    /**
     * @param string $courseId
     * @return void
     * @throws Exception
     */
    public function delete(string $courseId)
    {
        $query = <<<SQL
            UPDATE course
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE
                course_id = ?
            SQL;
        $params = [$courseId];
        $this->connection->executeQuery($query, $params);
    }

    /**
     * @param string $enrollmentId
     * @return void
     * @throws Exception
     */
    public function deleteStatus(string $enrollmentId)
    {
        $query = <<<SQL
            UPDATE course_status
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE
                enrollment_id = ?
            SQL;
        $params = [$enrollmentId];
        $this->connection->executeQuery($query, $params);
    }
}
