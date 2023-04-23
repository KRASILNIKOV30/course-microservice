<?php

namespace App\Model\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_status')]
class CourseStatus
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $enrollment_id;

    #[ORM\Column(type: 'integer')]
    private int $progress;

    #[ORM\Column(type: 'integer')]
    private int $duration;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        string $enrollment_id,
        ?int $progress = 0,
        ?int $duration = 0
    ) {
        $this->enrollment_id = $enrollment_id;
        $this->progress = $progress;
        $this->duration = $duration;
    }

    public function delete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    public function edit(int $progress, int $duration): void
    {
        $this->progress = $progress;
        $this->duration = $duration;
    }

    public function getEnrollmentId(): string
    {
        return $this->enrollment_id;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
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
