<?php

declare(strict_types=1);

namespace App\Model\Data;

class SaveCourseParams
{
    private string $courseId;
    /** @var string[] */
    private array $moduleIds;
    /** @var string[] */
    private array $requiredModuleIds;

    /**
     * @param string $courseId
     * @param string[] $moduleIds
     * @param string[] $requiredModuleIds
     */
    public function __construct(
        string $courseId,
        array $moduleIds,
        array $requiredModuleIds
    ) {
        $this->courseId = $courseId;
        $this->moduleIds = $moduleIds;
        $this->requiredModuleIds = $requiredModuleIds;
    }

    public function getCourseId(): string
    {
        return $this->courseId;
    }

    /**
     * @return string[]
     */
    public function getModuleIds(): array
    {
        return $this->moduleIds;
    }

    /**
     * @return string[]
     */
    public function getRequiredModuleIds(): array
    {
        return $this->requiredModuleIds;
    }
}
