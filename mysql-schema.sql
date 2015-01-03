-- User: policyd@localhost
-- GRANT USAGE ON *.* TO policyd@'localhost' IDENTIFIED BY '********';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON policyd.* TO policyd@'localhost';

-- -----------------------------------------------------
-- Schema policyd
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `policyd` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `policyd`;

-- -----------------------------------------------------
-- Table `policyd`.`ratelimit`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ratelimit` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`sender` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_bin' NOT NULL,
	`quota` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`used` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`expiry` INT(10) UNSIGNED DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `idx_sender` (`sender` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;
