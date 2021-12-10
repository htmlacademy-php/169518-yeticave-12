<?php
require_once('src/database.php');
require_once('src/helpers.php');
require_once('src/functions.php');
require_once('src/templates.php');
require_once('src/validate.php');

$connection = database_get_connection();
$categories = get_categories($connection);
$items = get_lots($connection);
$layout = templates_include_layout($user, $categories);

$main_content = include_template ('main.php', [
    'categories' => $categories, 
    'items' => $items
]);

$page_content = include_template ('layout.php', [
    'title' => 'YetiCave', 
    'categories' => $categories, 
    'header' => $layout['header'], 
    'top_menu' => '', 
    'main_content' => $main_content, 
    'single_lot_content' => ''
]);

print($page_content);
