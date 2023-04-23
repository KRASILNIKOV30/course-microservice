<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230423162947 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `course`.`course_module_status_all` (
                `enrollment_id` VARCHAR(36) NOT NULL,
                `module_id` VARCHAR(36) NOT NULL,
                `progress` DECIMAL(3,0) NOT NULL,
                `deleted_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`enrollment_id`, `module_id`),
                CONSTRAINT `fk_course_module_status_1`
                FOREIGN KEY (`module_id`)
                REFERENCES `course`.`course_material_all` (`module_id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION);
            CREATE VIEW course_module_status AS SELECT * FROM course_module_status_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
                DROP TABLE course_module_status_all;
                DROP VIEW course_module_status
            SQL
        );
    }
}
