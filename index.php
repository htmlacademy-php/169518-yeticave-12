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

$content = include_template ('main.php', [
    'categories' => $categories, 
    'items' => $items,
    'pages' => $pages,
    'pages_count' => $pages_count
]);

$page_content = include_template ('layout.php', [ 
    'header' => $layout['header'], 
    'top_menu' => '', 
    'categories' => $categories,
    'content' => $content
]);

print($page_content);


/**
 * Выводит из бд список открытых лотов без победителя
 *
 * @param  mixed $connection
 * @param  int $page_items
 * @param  int $offset
 * @return array массив с количеством лотов и массив с данными о лотах, разделенными по количеству позиций на странице
 */
function get_lots(mysqli $connection): array
{
    $query = [];
    $sql_query = "
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
    $res = mysqli_query($connection, $sql_query);
    $query['num'] = mysqli_num_rows($res);
    $query['list'] = pagination_query($connection, $query, $sql_query, $param = null);

    return $query;
}
