<?php

declare(strict_types=1);

namespace App\Model\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'course_material')]
class Module
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $module_id;

    //#[ORM\Column(type: 'string', unique: false, nullable: false)]
    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'modules')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'course_id')]
    private Course $course;

    #[ORM\Column(type: 'boolean', unique: false, nullable: false)]
    private bool $is_required;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable')]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct($module_id, $is_required, $course_id)
    {
        $this->module_id = $module_id;
        $this->is_required = $is_required;
        $this->course = $course_id;
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
        return $this->module_id;
    }

    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * @return string
     */
    public function getCourseId(): string
    {
        return $this->course->getId();
    }
}
