<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Common\Doctrine\DoctrineProvider;
use App\Common\Doctrine\Synchronization;
use App\Database\CourseModuleTable;
use App\Database\CourseStatusTable;
use App\Database\CourseTable;
use App\Database\EnrollmentTable;
use App\Database\ModuleStatusTable;

final class ServiceProvider
{
    private ?CourseService $courseService = null;
    private ?CourseTable $courseRepository = null;
    private ?EnrollmentTable $enrollmentRepository = null;
    private ?CourseModuleTable $courseModuleRepository = null;
    private ?CourseStatusTable $courseStatusTable = null;
    private ?ModuleStatusTable $moduleStatusTable = null;

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

    private function getCourseRepository(): CourseTable
    {
        if ($this->courseRepository === null) {
            $this->courseRepository = new CourseTable(
                DoctrineProvider::getConnection(),
                DoctrineProvider::getEntityManager()
            );
        }
        return $this->courseRepository;
    }

    private function getEnrollmentRepository(): EnrollmentTable
    {
        if ($this->enrollmentRepository === null) {
            $this->enrollmentRepository = new EnrollmentTable(
                DoctrineProvider::getConnection(),
                DoctrineProvider::getEntityManager()
            );
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

    private function getCourseStatusTable(): CourseStatusTable
    {
        if ($this->courseStatusTable === null) {
            $this->courseStatusTable = new CourseStatusTable(
                DoctrineProvider::getEntityManager()
            );
        }
        return $this->courseStatusTable;
    }

    private function getModuleStatusTable(): ModuleStatusTable
    {
        if ($this->moduleStatusTable === null) {
            $this->moduleStatusTable = new ModuleStatusTable(
                DoctrineProvider::getEntityManager()
            );
        }
        return $this->moduleStatusTable;
    }
}
