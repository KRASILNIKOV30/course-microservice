<?php

namespace App\Database;

use App\Model\Data\CourseStatusData;
use App\Model\Data\ModuleStatusData;
use App\Model\Domain\Course;
use App\Model\Domain\CourseStatus;
use App\Model\Domain\Module;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseStatusRepository
{
    private EntityRepository $repository;
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(CourseStatus::class);
    }

    public function findOne(string $enrollmentId): ?CourseStatus
    {
        return $this->repository->find($enrollmentId);
    }

    public function add(CourseStatus $courseStatus): void
    {
        $this->entityManager->persist($courseStatus);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @param string $enrollmentId
     * @param Course $course
     * @param CourseStatusData $courseStatusData
     * @return void
     */
    public function recalculate(string $enrollmentId, Course $course, CourseStatusData $courseStatusData): void
    {
        $modules = $course->getModules();
        $requiredModules = array_filter($modules, fn($module) => $module->isRequired());
        $moduleStatuses = $courseStatusData->getModules();
        $progress = $this->calculateProgress($requiredModules, $moduleStatuses);
        $duration = array_sum(array_map(fn($module) => $module->getDuration(), $moduleStatuses));

        $courseStatus = $this->findOne($enrollmentId);
        $courseStatus?->edit($progress, $duration);
        $this->flush();
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
