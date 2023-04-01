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
