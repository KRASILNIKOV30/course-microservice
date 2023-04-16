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

    #[ORM\Column(type: 'string', unique: false, nullable: false)]
    private string $course_id;

    #[ORM\Column(type: 'boolean', unique: false, nullable: false)]
    private bool $is_required;

    public function __construct($module_id, $is_required, $course_id)
    {
        $this->module_id = $module_id;
        $this->is_required = $is_required;
        $this->course_id = $course_id;
    }

    public function getModuleId(): string
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
        return $this->course_id;
    }
}
