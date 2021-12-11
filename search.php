<?php
require_once('src/database.php');
require_once('src/templates.php');
require_once('src/request.php');
require_once('src/helpers.php');
require_once('src/functions.php');

/*
 * Получение данных - Controller
 */
$connection = database_get_connection();
$categories = get_categories($connection);
$layout = templates_include_layout($user, $categories);
$items = get_lots($connection);

/*
 * Отображение - View
 */
if (isset($_GET['search']) && $_GET['search']) {
    $search = trim(strip_tags($_GET['search']));
}
else {
    $search = '';
}
if ($search) {
    $search_result = search_query($connection, $search); 

if(!empty($search_result)) {
    $content = include_template('search.php', [
        'search' => $search,
        'search_result' => $search_result
    ]);
}
else {
    $content = include_template('error.php', [
        'error' => 'Ничего не найдено'
    ]);
} 

$page_content = include_template('layout.php', [
    'header' => $layout['header'], 
    'top_menu' => $layout['top_menu'], 
    'categories' => $categories, 
    'main_content' => '', 
    'single_lot_content' => $content
]);

print($page_content);
}
else {
    header('Location: index.php');
}


/*
 * Бизнес-логика - Model
 */

function search_query(mysqli $connection, string $search): array {  
    $sql_search_query = "
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
WHERE MATCH(`heading`, `description`) AGAINST(?) ORDER BY `create` DESC";
        
    $stmt = db_get_prepare_stmt($connection, $sql_search_query, [$search]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $query = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    return $query;
}

