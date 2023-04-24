<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Common\Doctrine\DoctrineProvider;
use App\Common\Doctrine\Synchronization;
use App\Database\CourseModuleRepository;
use App\Database\CourseStatusRepository;
use App\Database\CourseRepository;
use App\Database\EnrollmentRepository;
use App\Database\ModuleStatusRepository;

final class ServiceProvider
{
    private ?CourseService $courseService = null;
    private ?CourseRepository $courseRepository = null;
    private ?EnrollmentRepository $enrollmentRepository = null;
    private ?CourseModuleRepository $courseModuleRepository = null;
    private ?CourseStatusRepository $courseStatusTable = null;
    private ?ModuleStatusRepository $moduleStatusTable = null;

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
                $this->getCourseModuleRepository(),
                $this->getCourseStatusTable(),
                $this->getModuleStatusTable()
            );
        }
        return $this->courseService;
    }

    private function getCourseRepository(): CourseRepository
    {
        if ($this->courseRepository === null) {
            $this->courseRepository = new CourseRepository(DoctrineProvider::getEntityManager());
        }
        return $this->courseRepository;
    }

    private function getEnrollmentRepository(): EnrollmentRepository
    {
        if ($this->enrollmentRepository === null) {
            $this->enrollmentRepository = new EnrollmentRepository(DoctrineProvider::getEntityManager());
        }
        return $this->enrollmentRepository;
    }

    private function getCourseModuleRepository(): CourseModuleRepository
    {
        if ($this->courseModuleRepository === null) {
            $this->courseModuleRepository = new CourseModuleRepository(DoctrineProvider::getEntityManager());
        }
        return $this->courseModuleRepository;
    }

    private function getCourseStatusTable(): CourseStatusRepository
    {
        if ($this->courseStatusTable === null) {
            $this->courseStatusTable = new CourseStatusRepository(DoctrineProvider::getEntityManager());
        }
        return $this->courseStatusTable;
    }

    private function getModuleStatusTable(): ModuleStatusRepository
    {
        if ($this->moduleStatusTable === null) {
            $this->moduleStatusTable = new ModuleStatusRepository(DoctrineProvider::getEntityManager());
        }
        return $this->moduleStatusTable;
    }
}
