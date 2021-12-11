<?php
require_once('src/database.php');
require_once('src/request.php');
require_once('src/helpers.php');
require_once('src/functions.php');
require_once('src/templates.php');
/*
 * Получение данных - Controller
 */
$connection = database_get_connection();
$categories = get_categories($connection);
$layout = templates_include_layout($user, $categories);
/*
 * Отображение - View
 */
if(is_get()) {
    $set_id = request_get_int('id');
    $single_item = show_lot($connection); 
    if(!empty($single_item)) {
    $content = include_template ('single-lot.php', [
        'categories' => $categories, 
        'user' => $user,
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
            'main_content' => ' ', 
            'single_lot_content' => $content, 
            'categories' => $categories
        ]);

    print($page_content);
}

else {
    header('HTTP/1.1 403 Forbidden');
}
/*
 * Бизнес-логика - Model
 */


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
function show_lot(mysqli $connection): array {
    $sql_single_lot = "
    SELECT l.`heading`, l.`description`, l.`image`, l.`first_price`, l.`finish`, c.`title` FROM lot l
    JOIN category c ON l.`category_id` = c.`id`
    WHERE l.`id` = ?";
        
    $single_lot_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $stmt = db_get_prepare_stmt($connection, $sql_single_lot, [$single_lot_id]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_array($res, MYSQLI_ASSOC) ?? [];
}

