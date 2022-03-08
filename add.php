<?php
require_once('src/database.php');
require_once('src/functions.php');
require_once('src/helpers.php');
require_once('src/request.php');
require_once('src/validate.php');

/*
 * Получение данных - Controller
 */
$connection = database_get_connection();
$categories = get_categories($connection);
$cats_ids = array_column($categories, 'id');
$layout = templates_include_layout($user, $categories);
$errors = [];
$lot_id = null;

if (!isset($_SESSION['user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}
if (request_is_post()) {
    $add_lot = get_form_data();
    $errors = validate_form_data($add_lot, array_column($categories, 'id'));
    $uploading = request_save_file('lot-img');
    $add_lot = validate_file($errors, $uploading, $add_lot);
    if (empty($errors)) {
        move_uploaded_file($uploading['tmp_name'], $add_lot['lot-img']);
        array_push($add_lot, $_SESSION['user']['id']);
        $lot_id = save_lot($connection, $add_lot);

        if (!is_null($lot_id)) {
            header('Location: lot.php?id=' . $lot_id);
            exit();
        }
    }
}

/*
 * Отображение - View
 */
$content = include_template('add.php', [
    'errors' => $errors,
    'categories' => $categories
]);

$page_content = include_template('layout.php', [
    'header' => $layout['header'],
    'top_menu' => $layout['top_menu'],
    'content' => $content,
    'categories' => $categories
]);

print($page_content);

