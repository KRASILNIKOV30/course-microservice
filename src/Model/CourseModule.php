<?php

namespace App\Model;

class CourseModule
{
    private string $moduleId;
    private bool $isRequired;

    public function __construct($moduleId, $isRequired)
    {
        $this->moduleId = $moduleId;
        $this->isRequired = $isRequired;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }
}
