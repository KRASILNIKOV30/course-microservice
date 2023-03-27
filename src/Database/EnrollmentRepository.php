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

    public function save(SaveEnrollmentParams $saveEnrollmentParams): bool
    {
        $enrollmentId = $saveEnrollmentParams->getEnrollmentId();
        $courseId = $saveEnrollmentParams->getEnrollmentId();
        if (!$this->insertEnrollment($enrollmentId, $courseId)) {
            return false;
        }

        return true;
    }

    private function insertEnrollment(string $enrollmentId, string $courseId): bool
    {
        $query = <<<SQL
            INSERT INTO course_enrollment
                (enrollment_id, course_id)
            VALUES 
                (?, ?)
            SQL;
        $params = [$enrollmentId, $courseId];
        try {
            $this->connection->execute($query, $params);
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }
}
