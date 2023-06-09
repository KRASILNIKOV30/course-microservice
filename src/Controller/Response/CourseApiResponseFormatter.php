<?php

namespace App\Controller\Response;

use App\Model\Data\CourseStatusData;
use App\Model\Data\ModuleStatusData;

class CourseApiResponseFormatter
{
    public static function formatCourseStatus(CourseStatusData $course): array
    {
        return [
            'enrollmentId' => $course->getEnrollmentId(),
            'modules' => array_map(fn($module) => self::formatModuleStatus($module), $course->getModules()),
            'progress' => $course->getProgress(),
            'duration' => $course->getDuration()
        ];
    }

    public static function formatModuleStatus(ModuleStatusData $module): array
    {
        return [
            'id' => $module->getModuleId(),
            'progress' => $module->getProgress(),
            'duration' => $module->getDuration()
        ];
    }
}
