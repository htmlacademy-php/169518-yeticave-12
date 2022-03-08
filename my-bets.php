<?php
require_once('src/database.php');
require_once('src/helpers.php');
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
} else {
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
