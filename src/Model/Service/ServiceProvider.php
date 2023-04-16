<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Common\Database\ConnectionProvider;
use App\Common\Doctrine\DoctrineProvider;
use App\Common\Doctrine\Synchronization;
use App\Database\CourseModuleTable;
use App\Database\CourseTable;
use App\Database\EnrollmentTable;

final class ServiceProvider
{
    private ?CourseService $courseService = null;
    private ?CourseTable $courseRepository = null;
    private ?EnrollmentTable $enrollmentRepository = null;
    private ?CourseModuleTable $courseModuleRepository = null;

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
            $synchronization = new Synchronization(DoctrineProvider::getConnection());
            $this->courseService = new CourseService(
                $synchronization,
                $this->getCourseRepository(),
                $this->getEnrollmentRepository(),
                $this->getCourseModuleRepository()
            );
        }
        return $this->courseService;
    }

    private function getCourseRepository(): CourseTable
    {
        if ($this->courseRepository === null) {
            $this->courseRepository = new CourseTable(ConnectionProvider::getConnection());
        }
        return $this->courseRepository;
    }

    private function getEnrollmentRepository(): EnrollmentTable
    {
        if ($this->enrollmentRepository === null) {
            $this->enrollmentRepository = new EnrollmentTable(DoctrineProvider::getConnection());
        }
        return $this->enrollmentRepository;
    }

    private function getCourseModuleRepository(): CourseModuleTable
    {
        if ($this->courseModuleRepository === null) {
            $this->courseModuleRepository = new CourseModuleTable(
                DoctrineProvider::getConnection(),
                DoctrineProvider::getEntityManager()
            );
        }
        return $this->courseModuleRepository;
    }
}
