<?php

require_once('vendor/autoload.php');

$transport = new Swift_SmtpTransport("phpdemo.ru", 25);
$transport->setUsername("keks@phpdemo.ru");
$transport->setPassword("htmlacademy");

$mailer = new Swift_Mailer($transport);


$sql = "SELECT u.`email`, u.`username`, l.`id`, l.`heading` 
FROM bet b JOIN lot l ON l.`id` = b.`bet_lot_id` 
JOIN users u ON b.`bet_user_id` = u.`id` WHERE l.`finish` <= NOW()
ORDER BY b.`date` DESC LIMIT 1;";

$res = mysqli_query($connection, $sql);

if ($res && mysqli_num_rows($res)) {
    $winner = mysqli_fetch_all($res, MYSQLI_ASSOC);

}

    $message = new Swift_Message();
        $message->setSubject('Поздравляем с победой');
        $message->setFrom(['keks@phpdemo.ru' => 'Yeticave']);
        $message->setBcc($winner['email']);
        $msg_content = include_template('email.php', ['winner' => $winner]);
        $message->setBody($msg_content, 'text/html');
        $result = $mailer->send($message);
        if ($result) {
            print("Рассылка успешно отправлена");
        }
        else {
            print("Не удалось отправить рассылку");
        }

