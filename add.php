<?php
require_once('src/helpers.php');
require_once('src/database.php');
require_once('src/functions.php');
require_once('src/request.php');
require_once('src/templates.php');
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
if(request_is_post()) {
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

/*
 * Бизнес-логика - Model
 */

/**
 * Забирает и фильтрует данные из формы добавления лота
 *
 * @return void
 */
function get_form_data() {
    return filter_input_array(INPUT_POST, [
        'lot-name' => FILTER_DEFAULT, 
        'lot-category' => FILTER_DEFAULT,
        'lot-description' => FILTER_DEFAULT,
        'lot-img' => FILTER_DEFAULT,
        'lot-rate' => FILTER_DEFAULT,
        'lot-step' => FILTER_DEFAULT,
        'lot-date' => FILTER_DEFAULT
    ], true);

}

/**
 * Проверяет загруженный файл и дает ему имя на сервере. Возвращает массив с данными нового лота
 *
 * @param  array $errors Массив с ошибками формы
 * @param  array $uploading Массив с данными загружаемого файла
 * @param  array $add_lot Массив с данными нового лота
 * @return array Массив с данными нового лота
 */
function validate_file(array &$errors, array $uploading, array $add_lot): array
{
    if (!$uploading['success']) {
        $errors['lot-img'] = $uploading['error'];
    }
    $add_lot['lot-img'] = $uploading['upload_name'];

    return $add_lot;
}

/**
 * Проверяет на ошибки форму добавления нового лота
 *
 * @param  array $add_lot Данные нового лота из формы
 * @param  array $cats_ids Массив с id категорий
 * @return array Ошибки
 */
function validate_form_data(array $add_lot, array $cats_ids): array {

    $required = ['lot-name', 'lot-category', 'lot-description', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

    $rules = [
        'lot-name' => function($value) {
            return validate_length($value, 5, 200);
        },
        'lot-category' => function($value) use ($cats_ids) {
            return validate_category($value, $cats_ids);
        },
        'lot-description' => function($value) {
            return validate_length($value, 5, 3000);
        },
        'lot-rate' => function($value) {
            return validate_numeric($value);
        },
        'lot-step' => function($value) {
            return validate_numeric($value);
        },
        'lot-date' => function($value) {
            return validate_date($value);
        }
    ];

    foreach ($add_lot as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }

        if (in_array($key, $required) && empty($value)) {
            $errors[$key] = 'Это поле надо заполнить';
        }
    }

    return array_filter($errors);
}

/**
 * Вносит данные нового лота из формы, возвращает id нового лота
 *
 * @param  mysqli $connection Данные для подключения к базе
 * @param  array $add_lot Массив с данными нового лота
 * @return int id нового лота в базе
 */
function save_lot(mysqli $connection, array $add_lot): int {

    $result = 'INSERT INTO lot 
    (`create`, `heading`, `category_id`, `description`, `image`, `first_price`, `price_step`, `finish`, `user_id`) 
    VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';
    
    $stmt = db_get_prepare_stmt($connection, $result, $add_lot);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($connection);
}