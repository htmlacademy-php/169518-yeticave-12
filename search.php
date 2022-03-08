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
if (isset($_GET['search']) && $_GET['search']) {
    $search = trim(strip_tags($_GET['search']));
} else {
    $search = '';
}
if ($search) {
    $items_count = search_query($connection, $search)['num'];
    $pages_count = ceil($items_count / PAGE_ITEMS);
    $pages = range(1, $pages_count);
    $search_result = search_query($connection, $search)['list'];
    $search_url = $_SERVER['PHP_SELF'] . '?search=' . $search;

    if (!empty($search_result)) {

        $content = include_template('search.php', [
            'search' => $search,
            'search_result' => $search_result,
            'pages' => $pages,
            'pages_count' => $pages_count,
            'search_url' => $search_url
        ]);
    } else {
        $content = include_template('error.php', [
            'error' => 'Ничего не найдено'
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
    header('Location: index.php');
}

