<?php

namespace App\Model\Data;

class SaveMaterialStatusParams
{
    private string $enrollmentId;
    private string $moduleId;
    private int $progress;
    private int $sessionDuration;

    public function __construct(
        $enrollmentId,
        $moduleId,
        $progress,
        $sessionDuration
    ) {
        $this->enrollmentId = $enrollmentId;
        $this->moduleId = $moduleId;
        $this->progress = $progress;
        $this->sessionDuration = $sessionDuration;
    }

    public function getEnrollmentId(): string
    {
        return $this->enrollmentId;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getSessionDuration(): int
    {
        return $this->sessionDuration;
    }
}
