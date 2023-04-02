<?php

namespace App\Database;

use App\Common\Database\Connection;

class CourseModuleRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function enroll(string $moduleId, string $enrollmentId)
    {
        $query = <<<SQL
            INSERT INTO course_module_status
                (enrollment_id, module_id, progress, duration)
            VALUES
                (?, ?, 0, 0)
            SQL;

        $params = [$enrollmentId, $moduleId];
        $this->connection->execute($query, $params);
    }

    public function getProgress(string $enrollmentId, string $moduleId): ?int
    {
        $query = <<<SQL
            SELECT
                progress
            FROM course_module_status
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;

        $params = [$enrollmentId, $moduleId];
        $stmt = $this->connection->execute($query, $params);
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$value) {
            return null;
        }
        return (int)$value['progress'];
    }

    public function getDuration(string $enrollmentId, string $moduleId): ?int
    {
        $query = <<<SQL
            SELECT
                duration
            FROM course_module_status
            WHERE enrollment_id = ?
                AND module_id = ?
            SQL;

        $params = [$enrollmentId, $moduleId];
        $stmt = $this->connection->execute($query, $params);
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$value) {
            return null;
        }
        return (int)$value['duration'];
    }
}
