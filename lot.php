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
$single_item = get_single_lot($connection, $lot_id);
$bets = get_lot_bets($connection, $lot_id);
$errors = [];

/*
 * Отображение - View
 */
if(is_get()) {
    if (!empty($single_item)) {
        $content = include_template('single-lot.php', [
            'categories' => $categories,
            'user' => $user,
            'bets' => $bets,
            'single_item' => $single_item
        ]);
    } else {
        $content = include_template('error.php', [
            'error' => 'Нет такого лота'
        ]);
    }

    $page_content = include_template('layout.php', [
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
    $errors = validate_new_bet($add_bet, $single_item['min_bet']);
    $new_bet = array($add_bet, $_GET['id'], $_SESSION['user']['id']);

    if (empty($errors)) {
        save_bet($connection, $new_bet);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
    else {
        $content = include_template('single-lot.php', [
            'categories' => $categories,
            'user' => $user,
            'bets' => $bets,
            'single_item' => $single_item,
            'errors' => $errors
        ]);
        $page_content = include_template('layout.php', [
            'header' => $layout['header'],
            'top_menu' => $layout['top_menu'],
            'content' => $content,
            'categories' => $categories
        ]);
        print($page_content);
    }
}






