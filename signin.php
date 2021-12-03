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
$layout = templates_include_layout($user, $categories);
$errors = [];

if(request_is_post()) {
    $login = $_POST;
    $errors = validate_login_data($login);

	$email = mysqli_real_escape_string($connection, $login['login-email']);

	$sql = "SELECT * FROM users WHERE email = '$email'";
	$res = mysqli_query($connection, $sql);
    if(!$res) {
        $errors['login-email'] = 'Неправильно введен электронный адрес';
}
	$logged = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;


    if (empty($errors)) {
        if (!password_verify($login['login-password'], $logged['pass'])) {
			$errors['login-password'] = 'Вы ввели неверный пароль';
        }
        else {
        $_SESSION['user'] = $logged;
        header('Location: index.php');
        exit();
        }
    }
}

/*
 * Отображение - View
 */
$content = include_template('signin.php', [
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
function validate_login_data($name) {
    $errors = [];
	$required = ['login-email', 'login-password'];
	foreach ($required as $field) {
	    if (empty($name[$field])) {
	        $errors[$field] = 'Это поле надо заполнить';
        }
    }

    return array_filter($errors);
}
