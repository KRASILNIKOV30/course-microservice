<?php

namespace App\Model\Data;

class CourseStatusData
{
    private string $enrollmentId;
    /**
     * @var ModuleStatusData[]
     */
    private array $modules;
    private int $progress;
    private int $duration;

    public function __construct(
        string $enrollmentId,
        array $modules,
        int $progress,
        int $duration
    ) {
        $this->enrollmentId = $enrollmentId;
        $this->modules = $modules;
        $this->progress = $progress;
        $this->duration = $duration;
    }

    public function getEnrollmentId(): string
    {
        return $this->enrollmentId;
    }

    /**
     * @return ModuleStatusData[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }
}
