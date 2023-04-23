<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230423142041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            CREATE TABLE `course`.`course_all` 
                (
                    `course_id` VARCHAR(36) NOT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NULL DEFAULT NULL,
                    `deleted_at` DATETIME NULL DEFAULT NULL,
                    PRIMARY KEY (`course_id`)
                );
            CREATE VIEW course AS SELECT * FROM course_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
                DROP VIEW course;
                DROP TABLE course_all
            SQL
        );
    }
}
