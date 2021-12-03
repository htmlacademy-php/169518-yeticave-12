<?php
require_once('src/helpers.php');
require_once('src/database.php');
require_once('src/functions.php');
require_once('src/templates.php');

$connection = database_get_connection();
$categories = get_categories($connection);
$items = get_lots($connection);
$layout = templates_include_layout($user, $categories);

if(is_get()) {
    $set_id = request_get_int('id');
    $single_item = show_lot($connection); 
    if(isset($single_item)) {
    $content = include_template ('single-lot.php', ['categories' => $categories, 'single_item' => $single_item]);
    
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
        $error = 'Данной страницы не существует на сайте';
        show_error($content, $error);
        }
    }

else {
    header('HTTP/1.1 403 Forbidden');
}

function is_get(): bool {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function request_get_int(string $name): int {
    $value = filter_input(INPUT_GET, $name);

    if (!is_numeric($value)) {
        exit();
    }
    return (int) $value;
}

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

