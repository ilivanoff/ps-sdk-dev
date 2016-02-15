/*
Created: 14.08.2010
Modified: 15.02.2016
Model: MySQL 5.1
Database: MySQL 5.1
*/

-- Create tables section -------------------------------------------------

-- Table crop_cell

CREATE TABLE crop_cell
(
  id_cell Int UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user Int UNSIGNED,
  n_cell Int UNSIGNED NOT NULL,
  dt_event Int UNSIGNED NOT NULL,
  v_img Varchar(255) NOT NULL,
  v_text Text,
 PRIMARY KEY (id_cell),
 UNIQUE id_cell (id_cell)
)
  DEFAULT CHARACTER SET utf8
  COLLATE utf8_general_ci
;

ALTER TABLE crop_cell ADD UNIQUE n_part (n_cell)
;

-- Create relationships section ------------------------------------------------- 

ALTER TABLE crop_cell ADD CONSTRAINT Relationship1 FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE NO ACTION ON UPDATE NO ACTION
;


