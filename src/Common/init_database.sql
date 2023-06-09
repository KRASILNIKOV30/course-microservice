-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema course
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema course
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `course` DEFAULT CHARACTER SET ascii ;
USE `course`;

-- -----------------------------------------------------
-- Table `course`.`course`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course`.`course_all` (
    `course_id` VARCHAR(36) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`course_id`))
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = ascii;


-- -----------------------------------------------------
-- Table `course`.`course_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course`.`course_status_all` (
    `enrollment_id` VARCHAR(36) NOT NULL,
    `progress` DECIMAL(3,0) NOT NULL,
    `duration` INT NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`enrollment_id`),
    UNIQUE INDEX `name_UNIQUE_1` (`enrollment_id` ASC) VISIBLE)
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = ascii;


-- -----------------------------------------------------
-- Table `course`.`course_material`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course`.`course_material_all` (
    `module_id` VARCHAR(36) NOT NULL,
    `course_id` VARCHAR(36) NOT NULL,
    `is_required` TINYINT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`module_id`),
    INDEX `fk_course_material_2_idx` (`course_id` ASC) VISIBLE,
    UNIQUE INDEX `index3` (`module_id` ASC) VISIBLE,
    CONSTRAINT `fk_course_material_2`
    FOREIGN KEY (`course_id`)
    REFERENCES `course`.`course` (`course_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = ascii;


-- -----------------------------------------------------
-- Table `course`.`course_module_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course`.`course_module_status_all` (
    `enrollment_id` VARCHAR(36) NOT NULL,
    `module_id` VARCHAR(36) NOT NULL,
    `progress` DECIMAL(3,0) NOT NULL,
    `duration` INT NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`enrollment_id`, `module_id`),
    UNIQUE INDEX `name_UNIQUE_2` (`module_id` ASC) VISIBLE,
    UNIQUE INDEX `name_UNIQUE_3` (`module_id` ASC) VISIBLE,
    CONSTRAINT `fk_course_module_status_1`
    FOREIGN KEY (`module_id`)
    REFERENCES `course`.`course_material` (`module_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = ascii;


-- -----------------------------------------------------
-- Table `course`.`course_enrollment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `course`.`course_enrollment_all` (
    `enrollment_id` VARCHAR(36) NOT NULL,
    `course_id` VARCHAR(45) NOT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`enrollment_id`),
    INDEX `fk_course_enrollment_2_idx` (`course_id` ASC) VISIBLE,
    CONSTRAINT `fk_course_enrollment_1`
    FOREIGN KEY (`enrollment_id`)
    REFERENCES `course`.`course_status` (`enrollment_id`),
    CONSTRAINT `fk_course_enrollment_2`
    FOREIGN KEY (`course_id`)
    REFERENCES `course`.`course` (`course_id`),
    CONSTRAINT `fk_course_enrollment_3`
    FOREIGN KEY (`enrollment_id`)
    REFERENCES `course`.`course_module_status` (`enrollment_id`))
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = ascii;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

DROP VIEW course;
DROP VIEW course_status;
DROP VIEW course_material;
DROP VIEW course_module_status;
DROP VIEW course_enrollment;

CREATE VIEW course AS SELECT * FROM course_all WHERE deleted_at IS NULL;
CREATE VIEW course_status AS SELECT * FROM course_status_all WHERE deleted_at IS NULL;
CREATE VIEW course_material AS SELECT * FROM course_material_all WHERE deleted_at IS NULL;
CREATE VIEW course_module_status AS SELECT * FROM course_module_status_all WHERE deleted_at IS NULL;
CREATE VIEW course_enrollment AS SELECT * FROM course_enrollment_all WHERE deleted_at IS NULL;

use course;
DROP TABLE course_enrollment_all;
DROP TABLE course_status_all;
DROP TABLE course_module_status_all;
DROP TABLE course_material_all;
DROP TABLE course_all;

UPDATE course_module_status
SET
	duration = 0
WHERE enrollment_id = '1'
	AND module_id = '1';

select * from course_module_status;

SELECT
	course_id
FROM course_enrollment
WHERE enrollment_id = '1';

UPDATE course_status
SET
	progress = 100,
	duration = 0
WHERE enrollment_id = '1';
SELECT * FROM course_material;





