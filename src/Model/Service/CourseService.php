<?php

namespace App\Model\Service;

use App\Common\Doctrine\Synchronization;
use App\Database\CourseModuleTable;
use App\Database\CourseTable;
use App\Database\EnrollmentTable;
use App\Model\Domain\Course;
use App\Model\Data\CourseStatusData;
use App\Model\Data\GetCourseStatusParams;
use App\Model\Data\ModuleStatusData;
use App\Model\Data\SaveCourseParams;
use App\Model\Data\SaveEnrollmentParams;
use App\Model\Data\SaveModuleStatusParams;
use App\Model\Domain\Module;
use App\Model\Exception\CourseNotFoundException;
use App\Model\Exception\EnrollmentNotFoundException;
use App\Model\Exception\ModuleStatusNotFoundException;
use Exception;
use Throwable;

class CourseService
{
    private Synchronization $synchronization;
    private CourseTable $courseRepository;
    private EnrollmentTable $enrollmentRepository;
    private CourseModuleTable $courseModuleRepository;

    public function __construct(
        Synchronization $synchronization,
        CourseTable $courseRepository,
        EnrollmentTable $enrollmentRepository,
        CourseModuleTable $courseModuleRepository
    ) {
        $this->synchronization = $synchronization;
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->courseModuleRepository = $courseModuleRepository;
    }

    /**
     * @param string $id
     * @return Course
     * @throws CourseNotFoundException
     */
    public function getCourse(string $id): Course
    {
        $course = $this->courseRepository->findOne($id);
        if ($course === null) {
            throw new CourseNotFoundException("Cannot find course with id $id");
        }
        return $course;
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
     * @param GetCourseStatusParams $params
     * @return CourseStatusData
     * @throws CourseNotFoundException
     * @throws EnrollmentNotFoundException
     * @throws ModuleStatusNotFoundException
     */
    public function getCourseStatus(GetCourseStatusParams $params): CourseStatusData
    {
        $enrollmentId = $params->getEnrollmentId();
        $courseId = $params->getCourseId();
        $course = $this->getCourse($courseId);
        $modules = $course->getModules();
        $moduleStatuses = [];
        foreach ($modules as $module) {
            $moduleStatuses[] = new ModuleStatusData(
                $module->getModuleId(),
                $this->getModuleProgress($enrollmentId, $module->getModuleId()),
                $this->getModuleDuration($enrollmentId, $module->getModuleId())
            );
        }

        return new CourseStatusData(
            $enrollmentId,
            $moduleStatuses,
            $this->getCourseProgress($enrollmentId),
            $this->getCourseDuration($enrollmentId)
        );
    }

    /**
     * @param SaveCourseParams $params
     * @return void
     * @throws Throwable
     */
    public function saveCourse(SaveCourseParams $params)
    {
        $this->synchronization->doWithTransaction(function () use ($params) {
            $this->courseRepository->save($params);
        });
    }

    /**
     * @param SaveEnrollmentParams $params
     * @return void
     * @throws Throwable
     */
    public function saveEnrollment(SaveEnrollmentParams $params)
    {
        $this->synchronization->doWithTransaction(function () use ($params) {
            $course = $this->getCourse($params->getCourseId());
            $this->courseRepository->enroll($params->getEnrollmentId(), $course);
            $modules = $course->getModules();
            foreach ($modules as $module) {
                $this->courseModuleRepository->enroll($module->getModuleId(), $params->getEnrollmentId());
            }
            $this->enrollmentRepository->save($params);
        });
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
            $this->courseModuleRepository->setProgress($enrollmentId, $moduleId, $params->getProgress());
            $this->courseModuleRepository->increaseDuration($enrollmentId, $moduleId, $params->getSessionDuration());
            $courseId = $this->enrollmentRepository->getCourseIdByEnrollmentId($enrollmentId);
            $course = $this->getCourse($courseId);
            $courseStatus = $this->getCourseStatus(new GetCourseStatusParams(
                $enrollmentId,
                $courseId
            ));
            $this->courseRepository->recalculateStatus($enrollmentId, $course, $courseStatus);
        });
    }

    /**
     * @param string $courseId
     * @return void
     * @throws Throwable
     */
    public function deleteCourse(string $courseId)
    {
        $this->synchronization->doWithTransaction(function () use ($courseId) {
            $enrollmentIds = $this->enrollmentRepository->listCourseEnrollmentIds($courseId);
            $course = $this->getCourse($courseId);
            $moduleIds = array_map(fn($module) => $module->getModuleId(), $course->getModules());
            foreach ($moduleIds as $moduleId) {
                foreach ($enrollmentIds as $enrollmentId) {
                    $this->courseModuleRepository->deleteStatus($enrollmentId, $moduleId);
                }
                $this->courseModuleRepository->delete($moduleId);
            }
            foreach ($enrollmentIds as $enrollmentId) {
                $this->courseRepository->deleteStatus($enrollmentId);
            }
            $this->enrollmentRepository->deleteCourseEnrollments($courseId);
            $this->courseRepository->delete($courseId);
        });
    }

    /**
     * @param string $enrollmentId
     * @return int
     * @throws EnrollmentNotFoundException
     */
    private function getCourseProgress(string $enrollmentId): int
    {
        $progress = $this->courseRepository->getProgress($enrollmentId);
        if ($progress === null) {
            throw new EnrollmentNotFoundException("Cannot find enrollment with id $enrollmentId");
        }
        return $progress;
    }

    /**
     * @param string $enrollmentId
     * @return int
     * @throws EnrollmentNotFoundException
     */
    private function getCourseDuration(string $enrollmentId): int
    {
        $duration = $this->courseRepository->getDuration($enrollmentId);
        if ($duration === null) {
            throw new EnrollmentNotFoundException("Cannot find enrollment with id $enrollmentId");
        }
        return $duration;
    }

    /**
     * @param string $enrollmentId
     * @param string $moduleId
     * @return int
     * @throws ModuleStatusNotFoundException
     */
    private function getModuleProgress(string $enrollmentId, string $moduleId): int
    {
        $progress = $this->courseModuleRepository->getProgress($enrollmentId, $moduleId);
        if ($progress === null) {
            $message = "Cannot find module status with enrollmentId $enrollmentId and moduleId $moduleId";
            throw new ModuleStatusNotFoundException($message);
        }
        return $progress;
    }

    /**
     * @param string $enrollmentId
     * @param string $moduleId
     * @return int
     * @throws ModuleStatusNotFoundException
     */
    private function getModuleDuration(string $enrollmentId, string $moduleId): int
    {
        $duration = $this->courseModuleRepository->getDuration($enrollmentId, $moduleId);
        if ($duration === null) {
            $message = "Cannot find module status with enrollmentId $enrollmentId and moduleId $moduleId";
            throw new ModuleStatusNotFoundException($message);
        }
        return $duration;
    }
}
