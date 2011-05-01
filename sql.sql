SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `oivindoh` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `oivindoh` ;

-- -----------------------------------------------------
-- Table `oivindoh`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `oivindoh`.`users` ;

CREATE  TABLE IF NOT EXISTS `oivindoh`.`users` (
  `email` VARCHAR(100) NOT NULL ,
  `password` VARCHAR(32) NULL ,
  `name` VARCHAR(100) NULL ,
  PRIMARY KEY (`email`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oivindoh`.`links`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `oivindoh`.`links` ;

CREATE  TABLE IF NOT EXISTS `oivindoh`.`links` (
  `ref` VARCHAR(32) NOT NULL ,
  `url` VARCHAR(200) NOT NULL ,
  `rss` VARCHAR(200) NULL ,
  `author` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  `frequency` INT NULL ,
  `clicks` INT NULL ,
  `title` VARCHAR(100) NULL ,
  PRIMARY KEY (`ref`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oivindoh`.`subjects`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `oivindoh`.`subjects` ;

CREATE  TABLE IF NOT EXISTS `oivindoh`.`subjects` (
  `unique` VARCHAR(32) NOT NULL ,
  `code` VARCHAR(15) NULL ,
  `term` VARCHAR(6) NULL ,
  `name` VARCHAR(50) NOT NULL ,
  `users_email` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`unique`) ,
  INDEX `fk_subjects_users1` (`users_email` ASC) ,
  CONSTRAINT `fk_subjects_users1`
    FOREIGN KEY (`users_email` )
    REFERENCES `oivindoh`.`users` (`email` )
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oivindoh`.`subjectlinks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `oivindoh`.`subjectlinks` ;

CREATE  TABLE IF NOT EXISTS `oivindoh`.`subjectlinks` (
  `subjects_unique` VARCHAR(32) NOT NULL ,
  `links_ref` VARCHAR(32) NOT NULL ,
  PRIMARY KEY (`subjects_unique`, `links_ref`) ,
  INDEX `fk_subjects_has_links_links1` (`links_ref` ASC) ,
  INDEX `fk_subjects_has_links_subjects1` (`subjects_unique` ASC) ,
  CONSTRAINT `fk_subjects_has_links_subjects1`
    FOREIGN KEY (`subjects_unique` )
    REFERENCES `oivindoh`.`subjects` (`unique` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_subjects_has_links_links1`
    FOREIGN KEY (`links_ref` )
    REFERENCES `oivindoh`.`links` (`ref` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
