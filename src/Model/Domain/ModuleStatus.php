<?php

namespace App\Model\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_module_status')]
class ModuleStatus
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $module_id;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $enrollment_id;

    #[ORM\Column(type: 'int')]
    private int $progress;

    #[ORM\Column(type: 'int')]
    private int $duration;

    public function __construct(
        string $module_id,
        string $enrollment_id,
        ?int $progress = 0,
        ?int $duration = 0
    ) {
        $this->module_id = $module_id;
        $this->enrollment_id = $enrollment_id;
        $this->progress = $progress;
        $this->duration = $duration;
    }

    /**
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->module_id;
    }

    /**
     * @return string
     */
    public function getEnrollmentId(): string
    {
        return $this->enrollment_id;
    }

    /**
     * @return int
     */
    public function getProgress(): int
    {
        return $this->progress;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }
}
