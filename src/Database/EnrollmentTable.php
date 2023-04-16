<?php

namespace App\Database;

use Doctrine\DBAL\Connection;
use App\Model\Data\SaveEnrollmentParams;
use Doctrine\DBAL\Exception;

class EnrollmentTable
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(SaveEnrollmentParams $saveEnrollmentParams): void
    {
        $enrollmentId = $saveEnrollmentParams->getEnrollmentId();
        $courseId = $saveEnrollmentParams->getCourseId();
        $this->insertEnrollment($enrollmentId, $courseId);
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

    /**
     * @param string $enrollmentId
     * @param string $courseId
     * @return void
     * @throws Exception
     */
    private function insertEnrollment(string $enrollmentId, string $courseId): void
    {
        $query = <<<SQL
            INSERT INTO course_enrollment
                (enrollment_id, course_id)
            VALUES 
                (?, ?)
            SQL;
        $params = [$enrollmentId, $courseId];
        $this->connection->executeQuery($query, $params);
    }
}
