<?php
require_once('src/database.php');
require_once('src/request.php');
require_once('src/helpers.php');
require_once('src/functions.php');
require_once('src/validate.php');

/*
 * Получение данных - Controller
 */
$connection = database_get_connection();
$categories = get_categories($connection);
$layout = templates_include_layout($user, $categories);
$lot_id = request_get_int('id');
$errors = [];
/*
 * Отображение - View
 */


if(is_get()) {
    $single_item = get_single_lot($connection, $lot_id); 
    $bets = get_lot_bets($connection, $lot_id);
    if(!empty($single_item)) {
    $content = include_template ('single-lot.php', [
        'categories' => $categories, 
        'user' => $user,
        'bets' => $bets,
        'single_item' => $single_item
    ]);
    
}
else {
    $content = include_template ('error.php', [
        'error' => 'Нет такого лота'
    ]);
}
    $page_content = include_template ('layout.php', [
            'header' => $layout['header'], 
            'top_menu' => $layout['top_menu'], 
            'content' => $content, 
            'categories' => $categories
        ]);

    print($page_content);
}

else {
    header('HTTP/1.1 403 Forbidden');
}
if(request_is_post()) {
    $add_bet = get_bet_data();
    $errors = validate_new_bet($add_bet);
    array_push($add_bet, $_GET['id']);
    array_push($add_bet, $_SESSION['user']['id']);
    
    if (empty($errors)) {
    save_bet($connection, $add_bet);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
    }
    
}

/*
 * Бизнес-логика - Model
 */
function get_bet_data() {
    return filter_input_array(INPUT_POST, [
        'new-bet' => FILTER_DEFAULT
    ], true);

}

function validate_new_bet(array $add_bet): array {
    $errors = [];
    $required = ['new-bet'];

    $rules = [
        'new-bet' => function($value) {
            return validate_numeric($value);
        }
    ];

    foreach ($add_bet as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }

        if (in_array($key, $required) && empty($value)) {
            $errors[$key] = 'Это поле надо заполнить';
        }

    }
    return array_filter($errors);
}
/**
 * Проверяет, какой id передан в get-запрос
 *
 * @param  mixed $name
 * @return int возвращает численный id
 */
function request_get_int(string $name): int {
    $value = filter_input(INPUT_GET, $name);

    if (!is_numeric($value)) {
        exit();
    }
    return (int) $value;
}

/**
 * Выбирает данные о лоте из базы
 *
 * @param  mixed $connection соединение с базой
 * @return array данные лота
 */
function get_single_lot(mysqli $connection, int $lot_id): array {
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

function get_lot_bets(mysqli $connection, int $lot_id): array {
    $sql_bets_in_lot = "SELECT 
    b.`price`,
    b.`date`,
    u.`username`,
    DATE_FORMAT(`date`, '%d.%m.%y в %H:%i') as `new_date`,
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


function save_bet(mysqli $connection, array $add_bet): int {

    $result = 'INSERT INTO bet (`date`, `price`, `bet_lot_id`, `bet_user_id`)
    VALUES (NOW(), ?, ?, ?)';
    
    $stmt = db_get_prepare_stmt($connection, $result, $add_bet);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($connection);
}