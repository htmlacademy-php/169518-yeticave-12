<?php
require_once('src/database.php');
require_once('src/request.php');
require_once('src/helpers.php');
require_once('src/functions.php');
/*
 * Получение данных - Controller
 */
$connection = database_get_connection();
$categories = get_categories($connection);
$layout = templates_include_layout($user, $categories);
/*
 * Отображение - View
 */
if (is_get()) {
    $set_category = request_get_category('name');
    $cat_count = show_cat($connection)['num'];
    $pages_count = ceil($cat_count / PAGE_ITEMS);
    $pages = range(1, $pages_count);
    $single_category = show_cat($connection)['list'];
    $category_url = $_SERVER['PHP_SELF'] . '?name=' . $set_category;

    if (!empty($single_category)) {
        $category_name = $single_category[0]['title'];
        $content = include_template('category.php', [
            'category_name' => $category_name,
            'single_category' => $single_category,
            'pages' => $pages,
            'pages_count' => $pages_count,
            'category_url' => $category_url
        ]);
    } else {
        $content = include_template('error.php', [
            'error' => 'Нет товаров в этой категории'
        ]);
    }

    $page_content = include_template('layout.php', [
        'header' => $layout['header'],
        'top_menu' => $layout['top_menu'],
        'categories' => $categories,
        'content' => $content
    ]);

    print($page_content);
} else {
    header('HTTP/1.1 403 Forbidden');
}

