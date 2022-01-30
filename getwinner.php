<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
require_once 'vendor/autoload.php';
 
/*
 * Получение данных - Controller
 */

$dsn = 'smtp://9fad5a5c464192:5b0785d3b0b8c9@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);

$connection = database_get_connection();
$finished_lots = find_finished_lots($connection);

if(!empty($finished_lots)) {

foreach($finished_lots as $arr) {
    foreach($arr as $key => $lot_id) {
        $lot_id = (int) $lot_id;
        $winner_arr = find_lot_winner($connection, $lot_id);
        foreach($winner_arr as $winner) {
            $message = new Email();
            $message->to($winner['email']);
            $message->from('keks@phpdemo.ru');
            $message->subject('Ваша ставка победила');
            $msg_content = include_template('email.php', ['winner' => $winner]);
            $message->html($msg_content, 'text/html');
            $mailer = new Mailer($transport);
            $mailer->send($message);
        } 
        $winner_id = $winner_arr[0]['bet_user_id'];
        set_lot_winner($connection, $winner_id, $lot_id);
    }
}
}

else {
    return null;
}
/*
 * Бизнес-логика - Model
 */

/*
выборка завершенных лотов со ставками
*/
function find_finished_lots(mysqli $connection): array {
    $sql = "SELECT b.`bet_lot_id`
    FROM lot l JOIN bet b ON b.`bet_lot_id` = l.`id` WHERE l.`finish` < NOW() AND l.`winner_user_id` IS NULL GROUP BY l.`id`";
    $result = mysqli_query($connection, $sql);
    $finished = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    return $finished;
}

/*
по айди лота ищем победителя
*/
function find_lot_winner(mysqli $connection, int $lot_id): array {
    $sql = "SELECT b.`bet_user_id`, u.`email` AS `email`, u.`username`, l.`id`, l.`heading` 
    FROM bet b JOIN users u ON b.`bet_user_id` = u.`id`
    JOIN lot l ON b.`bet_lot_id` = l.`id` 
    WHERE b.`id` = (SELECT MAX(`id`) FROM bet WHERE `bet_lot_id` = ?)";

    $stmt = db_get_prepare_stmt($connection, $sql, [$lot_id]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $winners_list = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    return $winners_list;
}

/*
записываем победителя в лот
 */
function set_lot_winner(mysqli $connection, $winner_id, $lot_id) {
    $sql = "UPDATE lot SET `winner_user_id` = ? WHERE `id` = ?";
    $stmt = db_get_prepare_stmt($connection, $sql, $data = [$winner_id, $lot_id]);
    mysqli_stmt_execute($stmt);
}


