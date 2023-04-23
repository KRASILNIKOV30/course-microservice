<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230423151841 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `course`.`course_status_all` (
                `enrollment_id` VARCHAR(36) NOT NULL,
                `progress` DECIMAL(3,0) NOT NULL,
                `duration` INT NULL DEFAULT NULL,
                `deleted_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`enrollment_id`),
                UNIQUE INDEX `name_UNIQUE_1` (`enrollment_id` ASC) VISIBLE,
                CONSTRAINT `fk_course_status_1`
                FOREIGN KEY (`enrollment_id`)
                REFERENCES `course`.`course_enrollment_all` (`enrollment_id`));
            CREATE VIEW course_status AS SELECT * FROM course_status_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
                DROP TABLE course_status_all;
                DROP VIEW course_status
            SQL
        );
    }
}
