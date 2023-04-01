<?php

namespace App\Model\Data;

class GetCourseStatusParams
{
    private string $enrollmentId;
    private string $courseId;

    public function __construct(string $enrollmentId, string $courseId)
    {
        $this->enrollmentId = $enrollmentId;
        $this->courseId = $courseId;
    }

    public function getEnrollmentId(): string
    {
        return $this->enrollmentId;
    }

    public function getCourseId(): string
    {
        return $this->courseId;
    }
}
