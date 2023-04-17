<?php

namespace App\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
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
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Module::class, cascade: ['persist'])]
    private Collection $modules;

    /**
     * @param string $course_id
     */
    public function __construct(string $course_id)
    {
        $this->course_id = $course_id;
        $this->modules = new ArrayCollection();
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

    public function addModule(string $id, bool $isRequired): void
    {
        $module = new Module(
            $id,
            $isRequired,
            $this
        );
        $this->modules->add($module);
    }
}
