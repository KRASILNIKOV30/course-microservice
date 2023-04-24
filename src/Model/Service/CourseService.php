<?php

namespace App\Model\Service;

use App\Common\Doctrine\Synchronization;
use App\Database\CourseModuleRepository;
use App\Database\CourseStatusRepository;
use App\Database\CourseRepository;
use App\Database\EnrollmentRepository;
use App\Database\ModuleStatusRepository;
use App\Model\Domain\Course;
use App\Model\Data\CourseStatusData;
use App\Model\Data\GetCourseStatusParams;
use App\Model\Data\ModuleStatusData;
use App\Model\Data\SaveCourseParams;
use App\Model\Data\SaveEnrollmentParams;
use App\Model\Data\SaveModuleStatusParams;
use App\Model\Domain\CourseStatus;
use App\Model\Domain\Enrollment;
use App\Model\Domain\Module;
use App\Model\Domain\ModuleStatus;
use App\Model\Exception\CourseNotFoundException;
use App\Model\Exception\EnrollmentNotFoundException;
use App\Model\Exception\ModuleStatusNotFoundException;
use Exception;
use Throwable;

class CourseService
{
    private Synchronization $synchronization;
    private CourseRepository $courseRepository;
    private EnrollmentRepository $enrollmentRepository;
    private CourseModuleRepository $courseModuleRepository;
    private CourseStatusRepository $courseStatusRepository;
    private ModuleStatusRepository $moduleStatusRepository;

    public function __construct(
        Synchronization $synchronization,
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        CourseModuleRepository $courseModuleRepository,
        CourseStatusRepository $courseStatusTable,
        ModuleStatusRepository $moduleStatusTable
    ) {
        $this->synchronization = $synchronization;
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->courseModuleRepository = $courseModuleRepository;
        $this->courseStatusRepository = $courseStatusTable;
        $this->moduleStatusRepository = $moduleStatusTable;
    }

    /**
     * @param string $id
     * @return Course
     * @throws CourseNotFoundException
     */
    private function getCourse(string $id): Course
    {
        $course = $this->courseRepository->findOne($id);
        if ($course === null) {
            throw new CourseNotFoundException("Cannot find course with id $id");
        }
        return $course;
    }

    /**
     * @param string $id
     * @return Enrollment
     * @throws EnrollmentNotFoundException
     */
    private function getEnrollment(string $id): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findOne($id);
        if ($enrollment === null) {
            throw new EnrollmentNotFoundException("Cannot find enrollment with id $id");
        }
        return $enrollment;
    }

    /**
     * @param string $enrollmentId
     * @return CourseStatus
     * @throws Exception
     */
    private function getCourseStatus(string $enrollmentId): CourseStatus
    {
        $courseStatus = $this->courseStatusRepository->findOne($enrollmentId);
        if ($courseStatus === null) {
            throw new Exception("Cannot find course staus with enrollmentId $enrollmentId");
        }
        return $courseStatus;
    }

    /**
     * @throws Exception
     */
    public function getModule(string $id): Module
    {
        $module = $this->courseModuleRepository->findOne($id);
        if (!$module) {
            throw new Exception("Cannot find module with id $id");
        }
        return $module;
    }

    /**
     * @param string $moduleId
     * @param string $enrollmentId
     * @return ModuleStatus
     * @throws ModuleStatusNotFoundException
     */
    public function getModuleStatus(string $moduleId, string $enrollmentId): ModuleStatus
    {
        $moduleStatus = $this->moduleStatusRepository->findOne($moduleId, $enrollmentId);
        if ($moduleStatus === null) {
            $message = "Cannot find module status with module id $moduleId and enrollment id $enrollmentId";
            throw new ModuleStatusNotFoundException($message);
        }
        return $moduleStatus;
    }


    /**
     * @param GetCourseStatusParams $params
     * @return CourseStatusData
     * @throws CourseNotFoundException
     * @throws ModuleStatusNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getCourseStatusData(GetCourseStatusParams $params): CourseStatusData
    {
        $enrollmentId = $params->getEnrollmentId();
        $moduleStatuses = $this->moduleStatusRepository->findAll($enrollmentId);
        $moduleStatusesData = [];
        foreach ($moduleStatuses as $moduleStatus) {
            $moduleStatusesData[] = new ModuleStatusData(
                $moduleStatus->getModuleId(),
                $moduleStatus->getProgress(),
                $moduleStatus->getDuration()
            );
        }
        $courseStatus = $this->getCourseStatus($enrollmentId);
        return new CourseStatusData(
            $enrollmentId,
            $moduleStatusesData,
            $courseStatus->getProgress(),
            $courseStatus->getDuration()
        );
    }

    /**
     * @param SaveCourseParams $params
     * @return void
     * @throws Throwable
     */
    public function saveCourse(SaveCourseParams $params): void
    {
        $this->synchronization->doWithTransaction(function () use ($params) {
            $course = new Course(
                $params->getCourseId(),
            );
            $moduleIds = $params->getModuleIds();
            $requiredModuleIds = $params->getRequiredModuleIds();
            foreach ($moduleIds as $moduleId) {
                $isRequired = in_array($moduleId, $requiredModuleIds, true);
                $course->addModule($moduleId, $isRequired);
            }
            $this->courseRepository->add($course);
            $this->courseRepository->flush();
        });
    }

    /**
     * @param SaveEnrollmentParams $params
     * @return void
     * @throws Throwable
     */
    public function saveEnrollment(SaveEnrollmentParams $params): void
    {
        $this->synchronization->doWithTransaction(function () use ($params) {
            $enrollment = new Enrollment(
                $params->getEnrollmentId(),
                $params->getCourseId()
            );
            $this->enrollmentRepository->add($enrollment);
            $this->enrollmentRepository->flush();

            $course = $this->getCourse($params->getCourseId());
            $progress = $this->getProgress($course);

            $courseStatus = new CourseStatus($params->getEnrollmentId(), $progress);
            $this->courseStatusRepository->add($courseStatus);
            $this->courseStatusRepository->flush();

            $modules = $course->getModules();
            foreach ($modules as $module) {
                $moduleStatus = new ModuleStatus(
                    $module->getId(),
                    $params->getEnrollmentId()
                );
                $this->moduleStatusRepository->add($moduleStatus);
            }
            $this->moduleStatusRepository->flush();
        });
    }

    private function getProgress(Course $course): int
    {
        $modules = $course->getModules();
        $requiredModules = array_filter($modules, fn($module) => $module->isRequired());
        return empty($requiredModules) ? 100 : 0;
    }

    /**
     * @param SaveModuleStatusParams $params
     * @return void
     * @throws Throwable
     */
    public function saveModuleStatus(SaveModuleStatusParams $params): void
    {
        $this->synchronization->doWithTransaction(function () use ($params) {
            $enrollmentId = $params->getEnrollmentId();
            $moduleId = $params->getModuleId();

            $moduleStatus = $this->getModuleStatus($moduleId, $enrollmentId);
            $moduleStatus->edit($params->getProgress(), $params->getSessionDuration());
            $this->moduleStatusRepository->flush();

            $enrollment = $this->getEnrollment($enrollmentId);
            $courseId = $enrollment->getCourseId();
            $course = $this->getCourse($courseId);
            $courseStatus = $this->getCourseStatusData(new GetCourseStatusParams(
                $enrollmentId,
                $courseId
            ));
            $this->recalculate($enrollmentId, $course, $courseStatus);
        });
    }

    //TODO: Убрать бизнес логику из репозитория
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

        $courseStatus = $this->courseStatusRepository->findOne($enrollmentId);
        $courseStatus?->edit($progress, $duration);
        $this->courseStatusRepository->flush();
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

    /**
     * @param string $courseId
     * @return void
     * @throws Throwable
     */
    public function deleteCourse(string $courseId): void
    {
        $this->synchronization->doWithTransaction(function () use ($courseId) {
            $enrollments = $this->enrollmentRepository->findAll($courseId);
            $course = $this->getCourse($courseId);
            $modules = $course->getModules();

            foreach ($enrollments as $enrollment) {
                foreach ($modules as $module) {
                    $moduleStatus = $this->moduleStatusRepository->findOne($module->getId(), $enrollment->getId());
                    $moduleStatus->delete();
                }
                $courseStatus = $this->courseStatusRepository->findOne($enrollment->getId());
                $courseStatus->delete();
                $enrollment->delete();
            }
            foreach ($modules as $module) {
                $module->delete();
            }
            $course->delete();
            $this->courseRepository->flush();
        });
    }
}
