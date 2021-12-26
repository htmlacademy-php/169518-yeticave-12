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

$content = include_template ('main.php', [
    'categories' => $categories, 
    'items' => $items
]);

$page_content = include_template ('layout.php', [ 
    'header' => $layout['header'], 
    'top_menu' => '', 
    'categories' => $categories,
    'content' => $content
]);

print($page_content);

/**
 * @param mysqli $connection
 * @return array [
 *  [
 *      'id' => int,
 *      'create' => string,
 *      'heading' => string,
 *      'first_price' => int,
 *      'price_step' => int,
 *      'finish' => string,
 *      'image' => string,
 *      'title => string
 *  ],
 *  ...
 * ]
 */
function get_lots(mysqli $connection): array
{
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
	l.`finish` > NOW()
ORDER BY
	`create` DESC";

    $result_items = mysqli_query($connection, $sql_items);
    $items = $result_items ? mysqli_fetch_all($result_items, MYSQLI_ASSOC) : [];

    return $items;
}
