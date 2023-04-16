<?php

namespace App\Model\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'course')]
class Course
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $course_id;

    /**
     * @var Collection<Module>
     */
    #[ORM\ManyToMany(targetEntity: Module::class)]
    #[ORM\JoinTable(name: 'course_material')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'course_id')]
    #[ORM\InverseJoinColumn(name: 'course_id', referencedColumnName: 'course_id')]
    private Collection $modules;

    /**
     * @param string $course_id
     * @param Collection<Module> $modules
     */
    public function __construct(string $course_id, Collection $modules)
    {
        $this->course_id = $course_id;
        $this->modules = $modules;
    }

    public function getId(): string
    {
        return $this->course_id;
    }

    /**
     * @return Module[]
     */
    public function getModules(): array
    {
        return $this->modules->toArray();
    }
}
