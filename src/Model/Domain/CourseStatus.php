<?php

namespace App\Model\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_status')]
class CourseStatus
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $enrollment_id;

    #[ORM\Column(type: 'int')]
    private int $progress;

    #[ORM\Column(type: 'int')]
    private int $duration;

    public function __construct(
        $enrollment_id,
        $progress,
        $duration,
    ) {
        $this->enrollment_id = $enrollment_id;
        $this->progress = $progress;
        $this->duration = $duration;
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
