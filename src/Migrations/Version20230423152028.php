<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230423152028 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `course`.`course_material_all` (
                `module_id` VARCHAR(36) NOT NULL,
                `course_id` VARCHAR(36) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `deleted_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`module_id`),
                INDEX `fk_course_material_2_idx` (`course_id` ASC) VISIBLE,
                UNIQUE INDEX `index3` (`module_id` ASC) VISIBLE);
            CREATE VIEW course_material AS SELECT * FROM course_material_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
                DROP TABLE course_material_all;
                DROP VIEW course_material
            SQL
        );
    }
}
