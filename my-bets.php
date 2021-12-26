<?php
require_once('src/helpers.php');
require_once('src/database.php');
require_once('src/functions.php');
require_once('src/request.php');
require_once('src/validate.php');

/*
 * Получение данных - Controller
 */
$connection = database_get_connection();
$categories = get_categories($connection);
$cats_ids = array_column($categories, 'id');
$layout = templates_include_layout($user, $categories);

if (!isset($_SESSION['user'])) {
    header('HTTP/1.1 403 Forbidden');
            exit();
}
else {
    $user_bets = get_my_bets($connection);
    $winner = get_winner($connection);   
}

/*
 * Отображение - View
 */
$content = include_template('my-bets.php', [
    'user_bets' => $user_bets,
    'winner' => $winner,
    'categories' => $categories
]);

$page_content = include_template('layout.php', [ 
    'header' => $layout['header'], 
    'top_menu' => $layout['top_menu'],  
    'content' => $content, 
    'categories' => $categories
]);

print($page_content);

/*
 * Бизнес-логика - Model
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

function get_winner(mysqli $connection): array {
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

function show_classes(array $bet_id_and_price, array $winning_bets): array {
    $class = [];
    foreach ($winning_bets as $bet) {
    if (htmlspecialchars($bet_id_and_price['id']) === htmlspecialchars($bet['ended'])) {
        if (htmlspecialchars($bet_id_and_price['price']) === htmlspecialchars($bet['max_price'])) {
            $class['item-end'] = 'rates__item--win';
            $class['timer-end'] = 'timer--win';
        }
        else {
            $class['item-end'] = 'rates__item--end';
            $class['timer-end'] = 'timer--end';
        }
    }
}
return array_filter($class);
}