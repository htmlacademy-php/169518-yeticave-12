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
if (!empty($finished_lots)) {

    foreach ($finished_lots as $arr) {
        $lot_id = (int)$arr['bet_lot_id'];
        $winner = find_lot_winner($connection, $lot_id);
        $message = new Email();
        $message->to($winner['email']);
        $message->from('keks@phpdemo.ru');
        $message->subject('Ваша ставка победила');
        $msg_content = include_template('email.php', ['winner' => $winner]);
        $message->html($msg_content, 'text/html');
        $mailer = new Mailer($transport);
        $mailer->send($message);
        $new_winner_id = $winner['bet_user_id'];
        set_lot_winner($connection, $new_winner_id, $lot_id);
    }
} else {
    return null;
}



