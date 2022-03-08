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
$layout = templates_include_layout($user, $categories);
$errors = [];
$user_id = null;
if (isset($_SESSION['user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}
if (request_is_post()) {
    $new_user = get_form_user_data();
    $errors = validate_form_user_data($connection, $new_user);

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
    'content' => $content,
    'categories' => $categories
]);

print($page_content);
