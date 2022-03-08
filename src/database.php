<?php
require_once('src/session.php');

define('DATABASE_HOST', 'localhost');
define('DATABASE_BASE_NAME', 'yeticave');
define('DATABASE_USER', 'root');
define('DATABASE_PASSWORD', '');
define('UPLOAD_MAX_SIZE', 2097152);
define('PAGE_ITEMS', 3);
define('CUR_PAGE', $_GET['page'] ?? 1);

/**
 * Connect to DB
 *
 * @return mysqli
 */
function database_get_connection(): mysqli
{
    $base = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_BASE_NAME);
    mysqli_set_charset($base, "utf8");
    if (!$base) {
        echo "Ошибка подключения к БД. Код ошибки: " . mysqli_connect_error();
        exit();
    }

    return $base;
}

/**
 * @param mysqli $connection
 * @return array [
 *  ['id' => int, 'title' => string, 'sympol' => string],
 *  ...
 * ]
 */
function get_categories(mysqli $connection): array
{
    $sql_categories = "SELECT `id`, `title`, `symbol` FROM category";
    $result_categories = mysqli_query($connection, $sql_categories);
    $categories = $result_categories ? mysqli_fetch_all($result_categories, MYSQLI_ASSOC) : [];

    return $categories;
}

/**
 * @param int|null $count number of bids
 * @return string text in the required case
 */
function get_bid_text(?int $count): string
{
    return empty($count) ? 'Стартовая цена' : ("$count " . get_noun_plural_form($count, 'ставка', 'ставки', 'ставок'));
}

/**
 * * Забирает и фильтрует введенные пользователем данные из формы
 * @return array|false|null
 */
function get_form_user_data()
{
    return filter_input_array(INPUT_POST, [
        'new-user-email' => FILTER_VALIDATE_EMAIL,
        'new-user-password' => FILTER_DEFAULT,
        'new-user-name' => FILTER_DEFAULT,
        'new-user-contact' => FILTER_DEFAULT
    ], true);

}

/**
 * Забирает и фильтрует данные из формы добавления лота
 *
 * @return void
 */
function get_form_data()
{
    return filter_input_array(INPUT_POST, [
        'lot-name' => FILTER_DEFAULT,
        'lot-category' => FILTER_DEFAULT,
        'lot-description' => FILTER_DEFAULT,
        'lot-img' => FILTER_DEFAULT,
        'lot-rate' => FILTER_DEFAULT,
        'lot-step' => FILTER_DEFAULT,
        'lot-date' => FILTER_DEFAULT
    ], true);

}

/**
 * Выгружает из бд товары в нужной категории
 *
 * @param mixed $connection
 * @return array массив с данными о товарах в категории
 */
function show_cat(mysqli $connection): array
{
    $query = [];
    $sql_query = "
    SELECT
	l.`id`,
	l.`create`,
	l.`heading`,
	IFNULL(b.`max_price`, l.`first_price`) `price`,
    l.`price_step`,
	l.`finish`,
	l.`image`,
	c.`title`,
	b.`count_bets`
FROM
	lot l
JOIN category c ON
	l.`category_id` = c.`id`
LEFT JOIN
(SELECT `bet_lot_id`, COUNT(`bet_lot_id`) AS `count_bets`, MAX(`price`) AS `max_price` FROM bet GROUP BY `bet_lot_id`) b ON
l.`id` = b.`bet_lot_id`
WHERE
	l.`finish` > CURDATE() AND c.`symbol` LIKE ? ORDER BY `create` DESC";

    $single_category_name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
    $stmt = db_get_prepare_stmt($connection, $sql_query, [$single_category_name]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $query['num'] = mysqli_num_rows($res);

    $query['list'] = pagination_query($connection, $query, $sql_query, $single_category_name);
    return $query;
}

/**
 * Выводит из бд список открытых лотов без победителя
 *
 * @param mixed $connection
 * @return array массив с количеством лотов и массив с данными о лотах, разделенными по количеству позиций на странице
 */
function get_lots(mysqli $connection): array
{
    $query = [];
    $sql_query = "
    SELECT
	l.`id`,
	l.`create`,
	l.`heading`,
	IFNULL(b.`max_price`, l.`first_price`) `price`,
    l.`price_step`,
	l.`finish`,
	l.`image`,
	c.`title`,
	b.`count_bets`
FROM
	lot l
JOIN category c ON
	l.`category_id` = c.`id`
LEFT JOIN
(SELECT `bet_lot_id`, COUNT(`bet_lot_id`) AS `count_bets`, MAX(`price`) AS `max_price` FROM bet GROUP BY `bet_lot_id`) b ON
l.`id` = b.`bet_lot_id`
WHERE
	l.`finish` > NOW() AND l.`winner_user_id` IS NULL
ORDER BY
	`create` DESC";
    $res = mysqli_query($connection, $sql_query);
    $query['num'] = mysqli_num_rows($res);
    $query['list'] = pagination_query($connection, $query, $sql_query, $param = null);

    return $query;
}


/**
 * Получает ставку из формы на странице лота
 *
 * @return array|false|null
 */
function get_bet_data() {
    return filter_input(INPUT_POST, 'new-bet', FILTER_DEFAULT, true);
}

/**
 * Выбирает данные о лоте из базы
 *
 * @param mixed $connection соединение с базой
 * @return array данные лота
 */
function get_single_lot(mysqli $connection, int $lot_id): array
{
    $sql_single_lot = "SELECT
    l.`id`,
    l.`user_id`,
	l.`create`,
	l.`heading`,
    l.`description`,
    l.`first_price`,
	IFNULL(b.`max_price`, l.`first_price`) `price`,
    TIMEDIFF(l.`finish`, NOW()) `diff`,
    l.`price_step`,
	l.`finish`,
	l.`image`,
	c.`title`,
	b.`count_bets`,
	b.`max_price`,
	(IFNULL(b.`max_price`, l.`first_price`) + l.`price_step`) `min_bet`
FROM
	lot l
JOIN category c ON
	l.`category_id` = c.`id`
LEFT JOIN
(SELECT `bet_lot_id`, COUNT(`bet_lot_id`) AS `count_bets`, MAX(`price`) AS `max_price` FROM bet GROUP BY `bet_lot_id`) b ON
l.`id` = b.`bet_lot_id`
    WHERE l.`id` = ?";

    $stmt = db_get_prepare_stmt($connection, $sql_single_lot, [$lot_id]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_array($res, MYSQLI_ASSOC) ?? [];
}

/**
 * * Выбирает данные о ставках на данный лот из бд
 * @param mysqli $connection
 * @param int $lot_id
 * @return array
 */
function get_lot_bets(mysqli $connection, int $lot_id): array
{
    $sql_bets_in_lot = "SELECT
    b.`price`,
    b.`date`,
    u.`username`,
    b.`bet_user_id`,
    DATE_FORMAT(`date`, '%d.%m.%y в %H:%i') AS `new_date`,
    TIMESTAMPDIFF(minute, b.`date`, NOW()) AS `duration`
    FROM bet b
    JOIN users u ON
u.`id` = b.`bet_user_id`
    JOIN lot l ON
l.`id` = b.`bet_lot_id`
    WHERE l.`id` = ?
    ORDER BY
	b.`date` DESC";

    $stmt = db_get_prepare_stmt($connection, $sql_bets_in_lot, [$lot_id]);
    mysqli_stmt_execute($stmt);
    $result_bets = mysqli_stmt_get_result($stmt);
    $showbets = $result_bets ? mysqli_fetch_all($result_bets, MYSQLI_ASSOC) : [];
    return $showbets;
}

/**
 * Выводит из бд ставки конкретного юзера
 *
 * @param mixed $connection
 * @return array массив с отфильтрованными данными о ставках одного юзера
 */
function get_my_bets(mysqli $connection): array
{
    $sql_my_bets = "
    SELECT
    l.`id`,
        l.`heading`,
        l.`finish`,
        l.`image`,
        c.`title`,
       b.`price`,
       b.`date`,
       TIMEDIFF(l.`finish`, NOW()) `diff`,
       DATE_FORMAT(`date`, '%d.%m.%y в %H:%i') as `new_date`,
       u.`contact`
    FROM
        lot l
    JOIN category c ON
        l.`category_id` = c.`id`
    JOIN users u ON
        u.`id` = l.`user_id`
    JOIN bet b ON
        l.`id` = b.`bet_lot_id`
    WHERE
        b.`bet_user_id` = ?
    ORDER BY
        b.`date` DESC";

    $this_user_id = $_SESSION['user']['id'];
    $stmt = db_get_prepare_stmt($connection, $sql_my_bets, [$this_user_id]);
    mysqli_stmt_execute($stmt);
    $result_my_bets = mysqli_stmt_get_result($stmt);
    $show_my_bets = $result_my_bets ? mysqli_fetch_all($result_my_bets, MYSQLI_ASSOC) : [];
    return $show_my_bets;
}

/**
 * Определяет победившие ставки
 *
 * @param mixed $connection
 * @return array $winner_arr
 */
function get_winner(mysqli $connection): array
{

    $sql_winner_bet = "
    SELECT
    b.`bet_lot_id` AS `ended`,
    MAX(b.`price`) AS `max_price`
    FROM bet b JOIN lot l
    ON b.`bet_lot_id` = l.`id`
    WHERE l.`finish` < NOW()
    GROUP BY b.`bet_lot_id`";

    $result_winner = mysqli_query($connection, $sql_winner_bet);
    $winner_arr = $result_winner ? mysqli_fetch_all($result_winner, MYSQLI_ASSOC) : [];

    return $winner_arr;
}


/**
 * Выборка завершенных лотов со ставками
 * @param mysqli $connection
 * @return array
 */
function find_finished_lots(mysqli $connection): array
{
    $sql = "SELECT b.`bet_lot_id`
    FROM lot l JOIN bet b ON b.`bet_lot_id` = l.`id` WHERE l.`finish` < NOW() AND l.`winner_user_id` IS NULL GROUP BY l.`id`";
    $result = mysqli_query($connection, $sql);
    $finished = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    return $finished;
}

/**
 * по айди лота ищем победителя
 * @param mysqli $connection
 * @param int $lot_id
 * @return array
 */
function find_lot_winner(mysqli $connection, int $lot_id): array
{
    $sql = "SELECT b.`bet_user_id`, u.`email` AS `email`, u.`username`, l.`id`, l.`heading`
    FROM bet b JOIN users u ON b.`bet_user_id` = u.`id`
    JOIN lot l ON b.`bet_lot_id` = l.`id`
    WHERE b.`id` = (SELECT MAX(`id`) FROM bet WHERE `bet_lot_id` = ?)";

    $stmt = db_get_prepare_stmt($connection, $sql, [$lot_id]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $winners_list = $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : [];
    return $winners_list;
}

/**
 * Записываем победителя в лот
 * @param mysqli $connection
 * @param $winner_id
 * @param $lot_id
 * @return void
 */
function set_lot_winner(mysqli $connection, $winner_id, $lot_id)
{
    $sql = "UPDATE lot SET `winner_user_id` = ? WHERE `id` = ?";
    $stmt = db_get_prepare_stmt($connection, $sql, $data = [$winner_id, $lot_id]);
    mysqli_stmt_execute($stmt);
}

/**
 * Вносит данные нового лота из формы, возвращает id нового лота
 *
 * @param mysqli $connection Данные для подключения к базе
 * @param array $add_lot Массив с данными нового лота
 * @return int id нового лота в базе
 */
function save_lot(mysqli $connection, array $add_lot): int
{

    $result = 'INSERT INTO lot
    (`create`, `heading`, `category_id`, `description`, `image`, `first_price`, `price_step`, `finish`, `user_id`)
    VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $result, $add_lot);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($connection);
}

/**
 * @param mysqli $connection
 * @param array $new_bet
 * @return int
 */
function save_bet(mysqli $connection, array $new_bet): int
{

    $result = 'INSERT INTO bet (`date`, `price`, `bet_lot_id`, `bet_user_id`)
    VALUES (NOW(), ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $result, $new_bet);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($connection);
}

/**
 * save_user сохраняет данные пользователя в базу
 *
 * @param mixed $connection соединение с базой данных
 * @param mixed $new_user массив с данными из формы регистрации нового пользователя
 * @return int айди нового пользователя
 */
function save_user(mysqli $connection, array $new_user): int
{
    $result = 'INSERT INTO users (`email`, `pass`, `username`, `contact`) VALUES (?, ?, ?, ?)';
    $stmt = db_get_prepare_stmt($connection, $result, $new_user);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($connection);
}

/**
 * Выводит из бд лоты, отвечающие поисковому запросу
 *
 * @param mixed $connection
 * @param string $search поисковый запрос
 * @return array массив с данными о лотах для вывода в результатах поиска
 */
function search_query(mysqli $connection, string $search): array
{
    $query = [];

    $sql_query = "
    SELECT
	l.`id`,
	l.`create`,
	l.`heading`,
	IFNULL(b.`max_price`, l.`first_price`) `price`,
    l.`price_step`,
	l.`finish`,
	l.`image`,
	c.`title`,
	b.`count_bets`
FROM
	lot l
JOIN category c ON
	l.`category_id` = c.`id`
LEFT JOIN
(SELECT `bet_lot_id`, COUNT(`bet_lot_id`) AS `count_bets`, MAX(`price`) AS `max_price` FROM bet GROUP BY `bet_lot_id`) b ON
l.`id` = b.`bet_lot_id`
WHERE MATCH(`heading`, `description`) AGAINST(?) ORDER BY `create` DESC";


    $stmt = db_get_prepare_stmt($connection, $sql_query, [$search]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $query['num'] = mysqli_num_rows($res);

    $query['list'] = pagination_query($connection, $query, $sql_query, $search);
    return $query;
}
