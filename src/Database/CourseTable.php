<?php

namespace App\Database;

use App\Model\Domain\Module;
use Doctrine\DBAL\Connection;
use App\Model\Domain\Course;
use App\Model\Data\CourseStatusData;
use App\Model\Data\ModuleStatusData;
use App\Model\Data\SaveCourseParams;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseTable
{
    private Connection $connection;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Course::class);
    }

    public function findOne(string $id): ?Course
    {
        return $this->repository->find($id);
    }

    /**
     * @param SaveCourseParams $saveCourseParams
     * @return void
     * @throws Exception
     */
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

    /**
     * @param string $enrollmentId
     * @param Course $course
     * @return void
     * @throws Exception
     */
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

        $this->connection->executeQuery($query, $params);
    }

    /**
     * @param string $enrollmentId
     * @return int|null
     * @throws Exception
     */
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
        $stmt = $this->connection->executeQuery($query, $params);
        $value = $stmt->fetchAssociative();
        if (!$value) {
            return null;
        }
        return (int)$value['progress'];
    }

    /**
     * @param string $enrollmentId
     * @return int|null
     * @throws Exception
     */
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
        $stmt = $this->connection->executeQuery($query, $params);
        $value = $stmt->fetchAssociative();
        if (!$value) {
            return null;
        }
        return (int)$value['duration'];
    }

    /**
     * @param string $enrollmentId
     * @param Course $course
     * @param CourseStatusData $courseStatus
     * @return void
     * @throws Exception
     */
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
        $this->connection->executeQuery($query, $params);
    }

    /**
     * @param string $courseId
     * @return void
     * @throws Exception
     */
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
        $this->connection->executeQuery($query, $params);
    }

    /**
     * @param string $enrollmentId
     * @return void
     * @throws Exception
     */
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
        $this->connection->executeQuery($query, $params);
    }

    /**
     * @param Module[] $requiredModules
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
     * @param string $courseId
     * @return void
     * @throws Exception
     */
    private function insertCourse(string $courseId): void
    {
        $query = 'INSERT INTO course (course_id) VALUES (?) ON DUPLICATE KEY UPDATE course_id = course_id;';
        $params = [$courseId];
        $this->connection->executeQuery($query, $params);
    }

    /**
     * @param string $moduleId
     * @param string $courseId
     * @param bool $isRequired
     * @return void
     * @throws Exception
     */
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
        $this->connection->executeQuery($query, $params);
    }
}
