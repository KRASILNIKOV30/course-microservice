<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230423165222 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE course_module_status_all 
                ADD COLUMN duration INT NULL DEFAULT NULL AFTER progress,
                ADD CONSTRAINT `fk_course_module_status_2`
                FOREIGN KEY (`enrollment_id`)
                REFERENCES `course`.`course_enrollment_all` (`enrollment_id`);
            DROP VIEW course_module_status;
            CREATE VIEW course_module_status AS SELECT * FROM course_module_status_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE course_module_status_all
                DROP COLUMN duration,
                DROP FOREIGN KEY fk_course_module_status_2
            SQL
        );
    }
}
