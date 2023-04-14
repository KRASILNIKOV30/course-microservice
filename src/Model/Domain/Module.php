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
    private string $id;

    #[ORM\Column(type: 'string', unique: false, nullable: false)]
    private string $courseId;

    #[ORM\Column(type: 'bool', unique: false, nullable: false)]
    private bool $isRequired;

    public function __construct($id, $isRequired)
    {
        $this->id = $id;
        $this->isRequired = $isRequired;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }
}
