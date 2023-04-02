<?php

namespace App\Model\Service;

use App\Common\Database\Synchronization;
use App\Database\CourseModuleRepository;
use App\Database\CourseQueryService;
use App\Database\CourseRepository;
use App\Database\EnrollmentRepository;
use App\Model\Course;
use App\Model\Data\CourseStatusData;
use App\Model\Data\GetCourseStatusParams;
use App\Model\Data\ModuleStatusData;
use App\Model\Data\SaveCourseParams;
use App\Model\Data\SaveEnrollmentParams;
use App\Model\Exception\CourseNotFoundException;
use App\Model\Exception\EnrollmentNotFoundException;
use App\Model\Exception\ModuleStatusNotFoundException;
use Throwable;

class CourseService
{
    private Synchronization $synchronization;
    private CourseRepository $courseRepository;
    private EnrollmentRepository $enrollmentRepository;
    private CourseModuleRepository $courseModuleRepository;
    private CourseQueryService $courseQueryService;

    public function __construct(
        Synchronization $synchronization,
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        CourseModuleRepository $courseModuleRepository,
        CourseQueryService $courseQueryService
    ) {
        $this->synchronization = $synchronization;
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->courseModuleRepository = $courseModuleRepository;
        $this->courseQueryService = $courseQueryService;
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
     * @param string $enrollmentId
     * @return int
     * @throws EnrollmentNotFoundException
     */
    private function getCourseProgress(string $enrollmentId): int
    {
        $progress = $this->courseQueryService->getCourseProgress($enrollmentId);
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
        $duration = $this->courseQueryService->getCourseDuration($enrollmentId);
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
        $progress = $this->courseQueryService->getModuleProgress($enrollmentId, $moduleId);
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
        $duration = $this->courseQueryService->getModuleDuration($enrollmentId, $moduleId);
        if ($duration === null) {
            $message = "Cannot find module status with enrollmentId $enrollmentId and moduleId $moduleId";
            throw new ModuleStatusNotFoundException($message);
        }
        return $duration;
    }
}
