<?php
require_once('src/database.php');
require_once('src/helpers.php');
require_once('src/functions.php');
require_once('src/validate.php');
require('getwinner.php');

$connection = database_get_connection();
$categories = get_categories($connection);

$items_count = get_lots($connection)['num'];
$pages_count = ceil($items_count / PAGE_ITEMS);
$pages = range(1, $pages_count);
$items = get_lots($connection)['list'];
$layout = templates_include_layout($user, $categories);

$content = include_template('main.php', [
    'categories' => $categories,
    'items' => $items,
    'pages' => $pages,
    'pages_count' => $pages_count
]);

$page_content = include_template('layout.php', [
    'header' => $layout['header'],
    'top_menu' => '',
    'categories' => $categories,
    'content' => $content
]);

print($page_content);



