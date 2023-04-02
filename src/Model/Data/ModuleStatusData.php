<?php

namespace App\Model\Data;

class ModuleStatusData
{
    private string $moduleId;
    private int $progress;
    private int $duration;

    public function __construct(string $moduleId, int $progress, int $duration)
    {
        $this->moduleId = $moduleId;
        $this->progress = $progress;
        $this->duration = $duration;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
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
