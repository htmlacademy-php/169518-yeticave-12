<?php

/**
 * @param mysqli $connection
 * @return array [
 *  ['id' => int, 'title' => string, 'sympol' => string],
 *  ...
 * ]
 */
function get_categories(mysqli $connection): array
{
    $sql_categories = "SELECT `id`, `title`, `symbol` FROM category";
    $result_categories = mysqli_query($connection, $sql_categories);
    $categories = $result_categories ? mysqli_fetch_all($result_categories, MYSQLI_ASSOC) : [];

    return $categories;
}

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

/**
 * @param int $count number of bids
 * @return string text in the required case 
 */
function get_bid_text(?int $count): string
{
    return empty($count) ? 'Стартовая цена' : ("$count " . get_noun_plural_form($count, 'ставка', 'ставки', 'ставок'));
}

/**
 * Форматирует цену по шаблону
 *
 * @param string $price целое число
 * @return $format_price . " ₽" отформатированная сумма вместе со знаком рубля
 */
function auction_price($price) {
    $format_price = ceil($price);
    $format_price = number_format($format_price, 0, ' ', ' ');
    return $format_price . " ₽";
}
/**
 * Выводит разницу во времени в формате 'ЧЧ:ММ'
 *
 * @param string $finishing дата в формате 'ГГГГ-ММ-ДД'
 * @return array $diff_array массив, где первый элемент — целое количество часов до даты, а второй — остаток в минутах
 */
function date_finishing($finishing) {
    $date_now = date_create('now');
    $date_finishing = date_create($finishing);
    $diff = (array) date_diff($date_now, $date_finishing);
    $diff_array = [
        'hours' => str_pad(($diff['d']*24 + $diff['h']), 2, "0", STR_PAD_LEFT),
        'minutes' => str_pad($diff['i'], 2, "0", STR_PAD_LEFT)
    ];
    return($diff_array);
}


// function show_error($content, $error) {
//    return include_template ('error.php', ['error' => $error]);
//}

