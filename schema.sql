CREATE DATABASE yeticave
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE yeticave;
CREATE TABLE category (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(64) NOT NULL,
    `symbol` VARCHAR(64) NOT NULL
);
CREATE INDEX `category_title_index` ON category(`title`);
CREATE TABLE users (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `username` VARCHAR(64) NOT NULL,
    `pass` VARCHAR(64) NOT NULL,
    `contact` TEXT(500) NOT NULL
);
CREATE TABLE lot (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `create`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `heading` VARCHAR(255) NOT NULL,
    `description` TEXT(500) NOT NULL,
    `image` VARCHAR(255) NOT NULL, 
    `first_price` INT NOT NULL,
    `finish`  DATETIME,
    `price_step` INT NOT NULL,
    `category_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    FOREIGN KEY (`category_id`) REFERENCES category(`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`)
);
CREATE INDEX lot_name_index ON lot(`heading`);
CREATE TABLE bet (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `price` INT NOT NULL,
    `bet_lot_id` INT NOT NULL,
    `bet_user_id` INT NOT NULL,
    FOREIGN KEY (`bet_lot_id`) REFERENCES lot(`id`),
    FOREIGN KEY (`bet_user_id`) REFERENCES users(`id`)
);
CREATE FULLTEXT INDEX lot_search ON lot(`heading`, `description`);