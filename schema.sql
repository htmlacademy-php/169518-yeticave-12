CREATE DATABASE yeticave
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE yeticave;
CREATE TABLE category (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(64) NOT NULL,
    `symbol` VARCHAR(64) NOT NULL
);
CREATE INDEX `category_name_index` ON category(`name`);
CREATE TABLE users (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `name` VARCHAR(64) NOT NULL,
    `pass` VARCHAR(64) NOT NULL,
    `contact` TEXT(500) NOT NULL
);
CREATE TABLE lot (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `create`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT(500) NOT NULL,
    `image` VARCHAR(255) NOT NULL, 
    `first_price` INT NOT NULL,
    `finish`  DATETIME,
    `price_step` INT NOT NULL,
    `category` INT NOT NULL,
    `user` INT NOT NULL,
    FOREIGN KEY (`category`) REFERENCES category(`id`),
    FOREIGN KEY (`user`) REFERENCES users(`id`)
);
CREATE INDEX lot_name_index ON lot (`name`);
CREATE TABLE bet (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `price` INT NOT NULL,
    `bet_lot_id` INT NOT NULL,
    `bet_user_id` INT NOT NULL,
    FOREIGN KEY (`bet_lot_id`) REFERENCES lot(`id`),
    FOREIGN KEY (`bet_user_id`) REFERENCES users(`id`)
);