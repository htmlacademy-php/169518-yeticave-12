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
$layout = templates_include_layout($is_auth, $user_name, $categories);
$errors = [];
$user_id = null;

if(request_is_post()) {
    $new_user = get_form_user_data();
    $errors = validate_form_user_data($new_user);
    $email = mysqli_real_escape_string($connection, $new_user['new-user-email']);
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $res = mysqli_query($connection, $sql);
    if (mysqli_num_rows($res) > 0) {
        $errors['new-user-email'] = 'Пользователь с этим email уже зарегистрирован';
    }
    if (empty($errors)) {
        $new_user['new-user-password'] = password_hash($new_user['new-user-password'], PASSWORD_DEFAULT);
        $user_id = save_user($connection, $new_user);
        
        if (!is_null($user_id)) {
            header('Location: signin.php');
            exit();
        }
    }
}

/*
 * Отображение - View
 */
$content = include_template('signup.php', [
    'errors' => $errors,
    'categories' => $categories
]);

$page_content = include_template('layout.php', [ 
    'header' => $layout['header'], 
    'top_menu' => $layout['top_menu'],  
    'main_content' => $content, 
    'single_lot_content' => '',
    'categories' => $categories
]);

print($page_content);

/*
 * Бизнес-логика - Model
 */
function get_form_user_data() {
    return filter_input_array(INPUT_POST, [
        'new-user-email' => FILTER_VALIDATE_EMAIL, 
        'new-user-password' => FILTER_DEFAULT,
        'new-user-name' => FILTER_DEFAULT,
        'new-user-contact' => FILTER_DEFAULT
    ], true);

}



function validate_form_user_data(array $new_user): array {

    $required = ['new-user-email', 'new-user-password', 'new-user-name', 'new-user-contact'];
    $errors = [];

    $rules = [
        'new-user-email' => function($value) {
            return validate_length($value, 5, 64);
        },
        'new-user-password' => function($value) {
            return validate_length($value, 3, 64);
        },
        'new-user-name' => function($value) {
            return validate_length($value, 2, 64);
        },
        'new-user-contact' => function($value) {
            return validate_length($value, 2, 500);
        }
    ];

    foreach ($new_user as $key => $value) {
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

function save_user(mysqli $connection, array $new_user) {
    $result = 'INSERT INTO users (`email`, `pass`, `username`, `contact`) VALUES (?, ?, ?, ?)';
    $stmt = db_get_prepare_stmt($connection, $result, $new_user);
    $res = mysqli_stmt_execute($stmt);
    return mysqli_insert_id($connection);
}
