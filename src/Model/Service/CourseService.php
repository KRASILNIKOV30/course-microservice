<?php

namespace App\Model\Service;

use App\Common\Database\Synchronization;
use App\Database\CourseModuleRepository;
use App\Database\CourseRepository;
use App\Database\EnrollmentRepository;
use App\Model\Course;
use App\Model\Data\SaveCourseParams;
use App\Model\Data\SaveEnrollmentParams;
use App\Model\Exception\CourseNotFoundException;
use Throwable;

class CourseService
{
    private Synchronization $synchronization;
    private CourseRepository $courseRepository;
    private EnrollmentRepository $enrollmentRepository;
    private CourseModuleRepository $courseModuleRepository;

    public function __construct(
        Synchronization $synchronization,
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        CourseModuleRepository $courseModuleRepository
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
        if (!$course) {
            throw new CourseNotFoundException("Cannot find course with id $id");
        }
        return $course;
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
}
