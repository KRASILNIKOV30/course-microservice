<?php

namespace App\Database;

use App\Common\Database\Connection;
use App\Model\Course;
use App\Model\CourseModule;
use App\Model\Data\CourseStatusData;
use App\Model\Data\ModuleStatusData;
use App\Model\Data\SaveCourseParams;

class CourseTable
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
            ON DUPLICATE KEY UPDATE
              enrollment_id = enrollment_id
            SQL;
        $params = [$enrollmentId];

        $this->connection->execute($query, $params);
    }

    public function getProgress(string $enrollmentId): ?int
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

    public function getDuration(string $enrollmentId): ?int
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

    public function recalculateStatus(string $enrollmentId, Course $course, CourseStatusData $courseStatus): void
    {
        $modules = $course->getModules();
        $requiredModules = array_filter($modules, fn($module) => $module->isRequired());
        $moduleStatuses = $courseStatus->getModules();
        $progress = $this->calculateProgress($requiredModules, $moduleStatuses);
        $duration = array_sum(array_map(fn($module) => $module->getDuration(), $moduleStatuses));

        $query = <<<SQL
            UPDATE course_status
            SET
                progress = $progress,
                duration = $duration
            WHERE enrollment_id = ?
            SQL;
        $params = [$enrollmentId];
        $this->connection->execute($query, $params);
    }

    public function delete(string $courseId)
    {
        $query = <<<SQL
            UPDATE course
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE
                course_id = ?
            SQL;
        $params = [$courseId];
        $this->connection->execute($query, $params);
    }

    public function deleteStatus(string $enrollmentId)
    {
        $query = <<<SQL
            UPDATE course_status
            SET
                deleted_at = CURRENT_TIMESTAMP
            WHERE
                enrollment_id = ?
            SQL;
        $params = [$enrollmentId];
        $this->connection->execute($query, $params);
    }

    /**
     * @param CourseModule[] $requiredModules
     * @param ModuleStatusData[] $moduleStatuses
     * @return int
     */
    private function calculateProgress(array $requiredModules, array $moduleStatuses): int
    {
        if (count($requiredModules) === 0) {
            return 100;
        }
        $requiredModuleIds = array_map(fn($module) => $module->getModuleId(), $requiredModules);
        $requiredModuleStatuses = array_filter(
            $moduleStatuses,
            fn($status) => in_array($status->getModuleId(), $requiredModuleIds)
        );
        $totalProgress = array_sum(array_map(
            fn($moduleStatus) => $moduleStatus->getProgress(),
            $requiredModuleStatuses
        ));

        return intval(round($totalProgress / count($requiredModules)));
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
        $query = 'INSERT INTO course (course_id) VALUES (?) ON DUPLICATE KEY UPDATE course_id = course_id;';
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
            ON DUPLICATE KEY UPDATE
              module_id = module_id
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
