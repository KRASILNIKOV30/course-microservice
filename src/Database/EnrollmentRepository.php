<?php

namespace App\Database;

use App\Common\Database\Connection;
use App\Model\Data\SaveEnrollmentParams;
use PDOException;

class EnrollmentRepository
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
        $stmt = $this->connection->execute($query, $params);
        return (string)$stmt->fetch(\PDO::FETCH_ASSOC)['course_id'];
    }

    private function insertEnrollment(string $enrollmentId, string $courseId): void
    {
        $query = <<<SQL
            INSERT INTO course_enrollment
                (enrollment_id, course_id)
            VALUES 
                (?, ?)
            SQL;
        $params = [$enrollmentId, $courseId];
        $this->connection->execute($query, $params);
    }
}
