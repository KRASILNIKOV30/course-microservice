<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230423145439 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE IF NOT EXISTS `course`.`course_enrollment_all` (
                `enrollment_id` VARCHAR(36) NOT NULL,
                `course_id` VARCHAR(45) NOT NULL,
                `deleted_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`enrollment_id`),
                INDEX `fk_course_enrollment_2_idx` (`course_id` ASC) VISIBLE,
                CONSTRAINT `fk_course_enrollment_2`
                FOREIGN KEY (`course_id`)
                REFERENCES `course`.`course_all` (`course_id`));
            CREATE VIEW course_enrollment AS SELECT * FROM course_enrollment_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
                DROP TABLE course_enrollment_all;
                DROP VIEW course_enrollment
            SQL
        );
    }
}
