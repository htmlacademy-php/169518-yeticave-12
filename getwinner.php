<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
require_once 'vendor/autoload.php';

$dsn = 'smtp://olga@ladobor.ru:7M4j6I9k@mail.hosting.reg.ru:25';
$transport = Transport::fromDsn($dsn);

$user_winner = [];
$connection = database_get_connection();
$sql = "SELECT u.`email`, u.`username`, l.`id`, l.`heading` 
FROM bet b JOIN lot l ON l.`id` = b.`bet_lot_id` 
JOIN users u ON b.`bet_user_id` = u.`id` WHERE l.`finish` <= NOW()
ORDER BY b.`date` DESC LIMIT 1";

$res = mysqli_query($connection, $sql);

if ($res && mysqli_num_rows($res)) {
    $user_winner = mysqli_fetch_all($res, MYSQLI_ASSOC);
}

foreach ($user_winner as $user) {

$message = new Email();
$message->to($user['email']);
$message->from('olga@ladobor.ru');
$message->subject('Ваша ставка победила');
$msg_content = include_template('email.php', ['user' => $user]);
$message->html($msg_content, 'text/html');

$mailer = new Mailer($transport);
$mailer->send($message);
}