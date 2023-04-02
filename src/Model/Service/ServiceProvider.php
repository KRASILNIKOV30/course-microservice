<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Common\Database\ConnectionProvider;
use App\Common\Database\Synchronization;
use App\Database\CourseModuleRepository;
use App\Database\CourseQueryService;
use App\Database\CourseRepository;
use App\Database\EnrollmentRepository;

final class ServiceProvider
{
    private ?CourseService $courseService = null;
    private ?CourseRepository $courseRepository = null;
    private ?EnrollmentRepository $enrollmentRepository = null;
    private ?CourseModuleRepository $courseModuleRepository = null;
    private ?CourseQueryService $courseQueryService = null;

    public static function getInstance(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public function getCourseService(): CourseService
    {
        if ($this->courseService === null) {
            $synchronization = new Synchronization(ConnectionProvider::getConnection());
            $this->courseService = new CourseService(
                $synchronization,
                $this->getCourseRepository(),
                $this->getEnrollmentRepository(),
                $this->getCourseModuleRepository(),
                $this->getCourseQueryService()
            );
        }
        return $this->courseService;
    }

    private function getCourseRepository(): CourseRepository
    {
        if ($this->courseRepository === null) {
            $this->courseRepository = new CourseRepository(ConnectionProvider::getConnection());
        }
        return $this->courseRepository;
    }

    private function getEnrollmentRepository(): EnrollmentRepository
    {
        if ($this->enrollmentRepository === null) {
            $this->enrollmentRepository = new EnrollmentRepository(ConnectionProvider::getConnection());
        }
        return $this->enrollmentRepository;
    }

    private function getCourseModuleRepository(): CourseModuleRepository
    {
        if ($this->courseModuleRepository === null) {
            $this->courseModuleRepository = new CourseModuleRepository(ConnectionProvider::getConnection());
        }
        return $this->courseModuleRepository;
    }

    private function getCourseQueryService(): CourseQueryService
    {
        if ($this->courseQueryService === null) {
            $this->courseQueryService = new CourseQueryService(ConnectionProvider::getConnection());
        }
        return $this->courseQueryService;
    }
}
