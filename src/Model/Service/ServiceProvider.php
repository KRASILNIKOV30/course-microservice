<?php

declare(strict_types=1);

namespace App\Model\Service;

use App\Common\Database\ConnectionProvider;
use App\Database\CourseRepository;

// TODO: убрать класс
final class ServiceProvider
{
    private ?CourseRepository $courseRepository = null;

    public static function getInstance(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public function getCourseRepository(): CourseRepository
    {
        if ($this->courseRepository === null) {
            $this->courseRepository = new CourseRepository(ConnectionProvider::getConnection());
        }
        return $this->courseRepository;
    }
}
