<?php

namespace App\Model\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'course')]
class Course
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;
    /**
     * @var Collection<Module>
     */
    #[ORM\OneToMany(targetEntity: Module::class)]
    private Collection $modules;

    /**
     * @param string $id
     * @param Collection<Module> $modules
     */
    public function __construct(string $id, Collection $modules)
    {
        $this->id = $id;
        $this->modules = $modules;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getModules(): array
    {
        return $this->modules->toArray();
    }
}