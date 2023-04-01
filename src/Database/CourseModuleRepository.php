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
}
