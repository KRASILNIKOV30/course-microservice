<?php

namespace App\Model;

class Course
{
    private string $id;
    /**
     * @var CourseModule[]
     */
    private array $modules;

    /**
     * @param string $id
     * @param CourseModule[] $modules
     */
    public function __construct(string $id, array $modules)
    {
        $this->id = $id;
        $this->modules = $modules;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return CourseModule[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }
}
