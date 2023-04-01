<?php

namespace App\Database;

use App\Common\Database\Connection;
use App\Model\Course;
use App\Model\CourseModule;
use App\Model\Data\SaveCourseParams;

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

    public function findOne(string $id): ?Course
    {
        $query = <<<SQL
            SELECT 
                c.course_id
            FROM course c
            WHERE c.course_id = ?
            SQL;

        $params = [$id];
        $stmt = $this->connection->execute($query, $params);
        if (!$stmt->fetch(\PDO::FETCH_ASSOC)) {
            return null;
        }

        return new Course($id, $this->getCourseModules($id));
    }

    public function enroll(string $enrollmentId, Course $course): void
    {
        $modules = $course->getModules();
        $requiredModules = array_filter($modules, fn($module) => $module->isRequired());
        $progress = empty($requiredModules) ? 100 : 0;
        $query = <<<SQL
            INSERT INTO course_status
                (enrollment_id, progress, duration)
            VALUES
                (?, $progress, 0)
            SQL;
        $params = [$enrollmentId];

        $this->connection->execute($query, $params);
    }

    /**
     * @param string $id
     * @return CourseModule[]
     */
    private function getCourseModules(string $id): array
    {
        $query = <<<SQL
            SELECT 
                cm.module_id,
                cm.is_required
            FROM course_material cm
            WHERE cm.course_id = ?;
            SQL;

        $params = [$id];
        $stmt = $this->connection->execute($query, $params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(
            fn($row) => $this->hydrateModuleData($row),
            $rows
        );
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

    private function hydrateModuleData(array $row)
    {
        return new CourseModule(
            $row['module_id'],
            $row['is_required']
        );
    }
}
