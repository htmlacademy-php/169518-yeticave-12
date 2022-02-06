<?php

function templates_include_layout(array $user, array $categories) {
    return [
        'header' => include_template('header.php', [
            'title' => 'YetiCave',
            'user' => $user
        ]),
        'top_menu' => include_template('top-menu.php', [
            'categories' => $categories
        ])
    ];
}

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
function auction_price(string $price): string {
    $format_price = ceil($price);
    $format_price = number_format($format_price, 0, ' ', ' ');
    return $format_price . ' ₽';
}

/**
 * Выводит разницу во времени в формате 'ЧЧ:ММ'
 *
 * @param string $finishing дата в формате 'ГГГГ-ММ-ДД'
 * @return array $diff_array массив, где первый элемент — целое количество часов до даты, а второй — остаток в минутах
 */
function date_finishing(string $finishing): array {
    $date_now = date_create('now');
    $date_finishing = date_create($finishing);
    $diff = (array) date_diff($date_now, $date_finishing);
    $diff_array = [
        'hours' => str_pad(($diff['d']*24 + $diff['h']), 2, "0", STR_PAD_LEFT),
        'minutes' => str_pad($diff['i'], 2, "0", STR_PAD_LEFT)
    ];
    return($diff_array);
}

/**
 * Показывает, сколько часов или минут назад сделана ставка: минут - если прошло меньше часа, часов - если прошло меньше суток. 
 * Если ставка была сделана вчера, пишет 'Вчера'
 *
 * @param  mixed $bet забирает дату ставки из базы
 * Возвращает строку или ничего, если прошло больше суток с момента ставки
 */
function bet_duration(string $bet): ?string {
    $bet_date_format = date_create($bet);
    $hours_minutes = date_format($bet_date_format, 'H:i');
    $bet_date = date_finishing($bet);
    $now = date_create('now');
    $hour_now = date_format($now, 'H');
    $min = (int)$bet_date['minutes'];
    $hour = (int)$bet_date['hours'];
    if ($hour <= $hour_now) {
        if ($bet_date['hours'] === '00') {
            return $min . ' ' . get_noun_plural_form($min, 'минута', 'минуты', 'минут') . ' назад'; 
        }
        else {   
            return $hour . ' ' . get_noun_plural_form($hour, 'час', 'часа', 'часов') . ' назад';
        }
    }
    elseif (($hour >= $hour_now) && ($hour <= $hour_now + 24)) {
        return 'Вчера в ' . $hours_minutes;
    }
    else {
        return null;
    }
}

