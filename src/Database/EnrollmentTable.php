<?php

namespace App\Database;

use App\Model\Domain\Enrollment;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class EnrollmentTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Connection $connection,
        EntityManagerInterface $entityManager,
    ) {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }

    public function add(Enrollment $enrollment): void
    {
        $this->entityManager->persist($enrollment);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function getCourseIdByEnrollmentId(string $enrollmentId): string
    {
        $query = <<<SQL
            SELECT
                course_id
            FROM course_enrollment
            WHERE enrollment_id = ?
            SQL;

        $params = [$enrollmentId];
        $stmt = $this->connection->executeQuery($query, $params);
        return (string)$stmt->fetch(\PDO::FETCH_ASSOC)['course_id'];
    }

    /**
     * @param string $courseId
     * @return string[]
     */
    public function listCourseEnrollmentIds(string $courseId): array
    {
        $query = <<<SQL
            SELECT
                enrollment_id
            FROM course_enrollment
            WHERE course_id = ?
            SQL;
        $params = [$courseId];
        $stmt = $this->connection->executeQuery($query, $params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => $row['enrollment_id'], $rows);
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
