<?php
require_once('src/database.php');
require_once('src/helpers.php');
require_once('src/functions.php');
require_once('src/validate.php');

require('getwinner.php');

$connection = database_get_connection();
$categories = get_categories($connection);

$cur_page = $_GET['page'] ?? 1;
$page_items = 9;
$offset = ($cur_page - 1) * $page_items;
$items_count = get_lots($connection, $page_items, $offset)['num'];
$pages_count = ceil($items_count / $page_items);
$pages = range(1, $pages_count);
$items = get_lots($connection, $page_items, $offset)['list'];
$layout = templates_include_layout($user, $categories);

$content = include_template ('main.php', [
    'categories' => $categories, 
    'items' => $items,
    'pages' => $pages,
    'pages_count' => $pages_count,
    'cur_page' => $cur_page
]);

$page_content = include_template ('layout.php', [ 
    'header' => $layout['header'], 
    'top_menu' => '', 
    'categories' => $categories,
    'content' => $content
]);

print($page_content);


function get_lots(mysqli $connection, $page_items, $offset): array
{
    $items = [];
    $sql_items = "
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
	l.`finish` > NOW() AND l.`winner_user_id` IS NULL
ORDER BY
	`create` DESC";
    $res = mysqli_query($connection, $sql_items);
    $items['num'] = mysqli_num_rows($res);
    $sql_items .= " LIMIT ? OFFSET ?";

    $stmt = db_get_prepare_stmt($connection, $sql_items, $data = [$page_items, $offset]);
    mysqli_stmt_execute($stmt);
    $result_items = mysqli_stmt_get_result($stmt);
    $items['list'] = $result_items ? mysqli_fetch_all($result_items, MYSQLI_ASSOC) : [];
 
    return $items;
}
