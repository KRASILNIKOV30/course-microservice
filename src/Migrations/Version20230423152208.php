<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230423152208 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE course_material_all
                ADD COLUMN is_required TINYINT NOT NULL AFTER deleted_at,
                ALGORITHM=INSTANT; 
            SQL
        );

        $this->addSql(<<<SQL
            SET foreign_key_checks = 0; 
            SQL
        );

        $this->addSql(<<<SQL
            ALTER TABLE course_material_all
               ADD CONSTRAINT `fk_course_material_2`
                FOREIGN KEY (`course_id`)
                REFERENCES `course`.`course_all` (`course_id`)
                ON DELETE RESTRICT
                ON UPDATE RESTRICT; 
            SQL
        );

        $this->addSql(<<<SQL
            SET foreign_key_checks = 1; 
            SQL
        );

        $this->addSql(<<<SQL
            DROP VIEW course_material
            SQL
        );

        $this->addSql(<<<SQL
            CREATE VIEW course_material AS SELECT * FROM course_material_all WHERE deleted_at IS NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE course_material_all
                DROP COLUMN is_required,
                DROP FOREIGN KEY fk_course_material_2
            SQL
        );
    }
}
