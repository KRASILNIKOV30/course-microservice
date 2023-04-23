<?php

namespace App\Model\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_enrollment')]
class Enrollment
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $enrollment_id;

    #[ORM\Column(type: 'string')]
    private string $course_id;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct($enrollment_id, $course_id)
    {
        $this->enrollment_id = $enrollment_id;
        $this->course_id = $course_id;
    }

    public function delete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getId(): string
    {
        return $this->enrollment_id;
    }

    public function getCourseId(): string
    {
        return $this->course_id;
    }
}
