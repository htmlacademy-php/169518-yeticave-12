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

$cur_page = $_GET['page'] ?? 1;
$page_items = 3;
$offset = ($cur_page - 1) * $page_items;

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
    $items_count = search_query($connection, $search, $page_items, $offset)['num']; 
    $pages_count = ceil($items_count / $page_items);
    $pages = range(1, $pages_count);
    $search_result = search_query($connection, $search, $page_items, $offset)['search']; 

if(!empty($search_result)) {

    $content = include_template('search.php', [
        'search' => $search,
        'search_result' => $search_result,
        'pages' => $pages,
        'pages_count' => $pages_count,
        'cur_page' => $cur_page
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
    'content' => $content
]);

print($page_content);
}
else {
    header('Location: index.php');
}


/*
 * Бизнес-логика - Model
 */

/**
 * Выводит из бд лоты, отвечающие поисковому запросу
 *
 * @param  mixed $connection
 * @param  string $search поисковый запрос
 * @return array массив с данными о лотах для вывода в результатах поиска
 */
function search_query(mysqli $connection, string $search, int $page_items, int $offset): array {  
    $query = [];

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
    $query['num'] = mysqli_num_rows($res);
 
    $sql_search_query .= " LIMIT ? OFFSET ?";


$stmt = db_get_prepare_stmt($connection, $sql_search_query, $data = [$search, $page_items, $offset]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $query['search'] = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    return $query;
}
