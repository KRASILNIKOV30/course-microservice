<?php

namespace App\Model\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_status')]
class CourseStatus
{
    #[ORM\Id]
    private string $enrollment_id;

    #[ORM\Column(type: 'int')]
    private int $progress;

    #[ORM\Column(type: 'int')]
    private int $duration;

    public function __construct(
        string $enrollment_id,
        ?int $progress = 0,
        ?int $duration = 0
    ) {
        $this->enrollment_id = $enrollment_id;
        $this->progress = $progress;
        $this->duration = $duration;
    }

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
