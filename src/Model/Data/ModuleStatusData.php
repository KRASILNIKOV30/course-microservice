<?php

namespace App\Model\Data;

class ModuleStatusData
{
    private string $moduleId;
    private int $progress;

    public function __construct(string $moduleId, int $progress)
    {
        $this->moduleId = $moduleId;
        $this->progress = $progress;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }
}
