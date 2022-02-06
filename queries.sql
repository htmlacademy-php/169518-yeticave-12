-- добавляю категории
INSERT INTO category (`title`, `symbol`)
VALUES 
('Доски и лыжи', 'boards'), 
('Крепления', 'attachment'), 
('Ботинки', 'boots'), 
('Одежда', 'clothing'), 
('Инструменты', 'tools'),
('Разное', 'other');

-- добавляю пользователей
INSERT INTO users (`email`, `username`, `pass`, `contact`)
VALUES 
('ya1@ya.ru', 'Оля', 'pass1', 'Москва'), 
('ya2@ya.ru', 'Ваня', 'pass2', 'Москва');

-- добавляю лоты
INSERT INTO lot (`heading`, `description`, `image`, `first_price`, `finish`, `price_step`, `category_id`, `user_id`)
VALUES 
('2014 Rossignol District Snowboard', 'Сноуборд', 'img/lot-1.jpg', 10999, '2021-08-24', 1, 1, 1),
('DC Ply Mens 2016/2017 Snowboard', 'Сноуборд', 'img/lot-2.jpg', 159999, '2021-08-24', 1, 1, 1),
('Крепления Union Contact Pro 2015 года размер L/XL', 'Крепления', 'img/lot-3.jpg', 8000, '2021-08-24', 100, 2, 1),
('Ботинки для сноуборда DC Mutiny Charocal', 'Ботинки', 'img/lot-4.jpg', 10999, '2021-08-24', 1, 3, 2),
('Куртка для сноуборда DC Mutiny Charocal', 'Куртка', 'img/lot-5.jpg', 7500, '2021-08-24', 500, 4, 2),
('Маска Oakley Canopy', 'Маска', 'img/lot-6.jpg', 5400, '2021-08-24', 100, 6, 2);

-- добавляю ставки
INSERT INTO bet (`price`, `bet_lot_id`, `bet_user_id`)
VALUES 
(11000, 1, 1), 
(11001, 1, 1), 
(5500, 6, 2),
(5600, 6, 2);

-- получаю все категории
SELECT `id`, `title`, `symbol` FROM category;

-- получаю самые новые, открытые лоты. Каждый лот должен включать название, стартовую цену, ссылку на изображение, цену, название категории
SELECT l.`id`, l.`create`, l.`heading`, l.`first_price`, l.`image`, c.`title` FROM lot l
JOIN category c ON l.`category_id` = c.`id`
WHERE l.`finish` > CURDATE();

-- показываю лот по его ID. Получаю также название категории, к которой принадлежит лот
SELECT l.`heading`, c.`id`, c.`title` FROM lot l
JOIN category c ON l.`category_id` = c.`id`
WHERE l.`id` = 6;

-- получаю список ставок для лота по его идентификатору с сортировкой по дате
SELECT `price` FROM bet WHERE `bet_lot_id` = 1 ORDER BY `date` ASC;

-- обновляю название лота по его идентификатору
UPDATE lot
SET `heading` = '2020 Ботинки для сноуборда DC Mutiny Charocal'
WHERE `id` = 4;

-- записываю id победителя в таблицу lot
UPDATE lot SET `winner_user_id` = ? WHERE `id` = ?;

-- увеличить время закрытия лота на неделю
-- UPDATE lot SET finish = DATE_ADD(finish, INTERVAL 21 DAY);