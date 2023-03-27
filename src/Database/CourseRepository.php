<?php

namespace App\Database;

use App\Common\Database\Connection;
use App\Model\Data\SaveCourseParams;
use PDOException;

class CourseRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(SaveCourseParams $saveCourseParams): void
    {
        $courseId = $saveCourseParams->getCourseId();
        $this->insertCourse($courseId);
        $moduleIds = $saveCourseParams->getModuleIds();
        $requiredModuleIds = $saveCourseParams->getRequiredModuleIds();
        foreach ($moduleIds as $moduleId) {
            $isRequired = in_array($moduleId, $requiredModuleIds, true);
            $this->insertCourseMaterial($moduleId, $courseId, $isRequired);
        }
    }

    private function insertCourse(string $courseId): void
    {
        $query = 'INSERT INTO course (course_id) VALUES (?);';
        $params = [$courseId];

        $this->connection->execute($query, $params);
    }

    private function insertCourseMaterial(string $moduleId, string $courseId, bool $isRequired): void
    {
        $query = <<<SQL
            INSERT INTO course_material
                (module_id, course_id, is_required)
            VALUES 
                (:module_id, :course_id, :is_required)
            SQL;

        $params = [
            ':module_id' => $moduleId,
            ':course_id' => $courseId,
            ':is_required' => intval($isRequired),
        ];
        $this->connection->execute($query, $params);
    }
}
