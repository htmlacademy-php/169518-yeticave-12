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
if(is_get()) {
    $set_category = request_get_category('name');
    $single_category = show_cat($connection); 

if(!empty($single_category)) {
    $category_name = $single_category[0]['title'];
    $content = include_template('category.php', [
        'category_name' => $category_name,
        'single_category' => $single_category
    ]);
}
else {
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
}
else {
    header('HTTP/1.1 403 Forbidden');
}

/*
 * Бизнес-логика - Model
 */

function request_get_category(string $name): string {
    $value = filter_input(INPUT_GET, $name);

    if (is_numeric($value)) {
        exit();
    }
    return (string) $value;
}

function show_cat(mysqli $connection): array {  
    $sql_single_category = "
    SELECT
	l.`id`,
	l.`create`,
	l.`heading`,
	IFNULL(b.`max_price`, l.`first_price`) `price`,
    l.`price_step`,
	l.`finish`,
	l.`image`,
	c.`title`,
	b.`count_bets`
FROM
	lot l
JOIN category c ON
	l.`category_id` = c.`id`
LEFT JOIN 
(SELECT `bet_lot_id`, COUNT(`bet_lot_id`) AS `count_bets`, MAX(`price`) AS `max_price` FROM bet GROUP BY `bet_lot_id`) b ON
l.`id` = b.`bet_lot_id`
WHERE
	l.`finish` > CURDATE() AND c.`symbol` LIKE ? ORDER BY `create` DESC";
        
    $single_category_name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
    $stmt = db_get_prepare_stmt($connection, $sql_single_category, [$single_category_name]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $cat = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    return $cat;
}