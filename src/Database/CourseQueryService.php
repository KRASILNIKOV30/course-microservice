<?php

namespace App\Database;

use App\Common\Database\Connection;

class CourseQueryService
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getCourseProgress(string $enrollmentId): ?int
    {
        $query = <<<SQL
            SELECT
                progress
            FROM course_status
            WHERE
                enrollment_id = ?
            SQL;

        $params = [$enrollmentId];
        $stmt = $this->connection->execute($query, $params);
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$value) {
            return null;
        }
        return (int)$value['progress'];
    }

    public function getCourseDuration(string $enrollmentId): ?int
    {
        $query = <<<SQL
            SELECT
                duration
            FROM course_status
            WHERE
                enrollment_id = ?
            SQL;

        $params = [$enrollmentId];
        $stmt = $this->connection->execute($query, $params);
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$value) {
            return null;
        }
        return (int)$value['duration'];
    }

    public function getModuleProgress(string $enrollmentId, string $moduleId): ?int
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

    public function getModuleDuration(string $enrollmentId, string $moduleId): ?int
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
