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

    public function add(Course $course): void
    {
        $this->entityManager->persist($course);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
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
        $requiredModuleIds = array_map(fn($module) => $module->getId(), $requiredModules);
        $requiredModuleStatuses = array_filter(
            $moduleStatuses,
            fn($status) => in_array($status->getModuleId(), $requiredModuleIds)
        );
        $totalProgress = array_sum(array_map(
            fn($moduleStatus) => $moduleStatus->getProgress(),
            $requiredModuleStatuses
        ));

        return intval(floor($totalProgress / count($requiredModules)));
    }
}
