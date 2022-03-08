<?php

/**
 * Формирует header и верхнее меню сайта из шаблонов
 *
 * @param array $user
 * @param array $categories
 * @return array
 */
function templates_include_layout(array $user, array $categories)
{
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
 * Форматирует цену по шаблону
 *
 * @param string $price целое число
 * @return string $format_price . " ₽" отформатированная сумма вместе со знаком рубля
 */
function auction_price(string $price): string
{
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
function date_finishing(string $finishing): array
{
    $date_now = date_create('now');
    $date_finishing = date_create($finishing);
    $diff = (array)date_diff($date_now, $date_finishing);
    $diff_array = [
        'hours' => str_pad(($diff['d'] * 24 + $diff['h']), 2, "0", STR_PAD_LEFT),
        'minutes' => str_pad($diff['i'], 2, "0", STR_PAD_LEFT)
    ];
    return ($diff_array);
}

/**
 * Показывает, сколько часов или минут назад сделана ставка: минут - если прошло меньше часа, часов - если прошло меньше суток.
 * Если ставка была сделана вчера, пишет 'Вчера'
 *
 * @param mixed $bet забирает дату ставки из базы
 * Возвращает строку или ничего, если прошло больше суток с момента ставки
 */
function bet_duration(string $bet): ?string
{
    $bet_date_format = date_create($bet);
    $hours_minutes = date_format($bet_date_format, 'H:i');
    $bet_date = date_finishing($bet);
    $now = date_create('now');
    $hour_now = date_format($now, 'H');
    $min = (int)$bet_date['minutes'];
    $hour = (int)$bet_date['hours'];
    if ($hour <= $hour_now) {
        if ($bet_date['hours'] == '00') {
            return $min . ' ' . get_noun_plural_form($min, 'минута', 'минуты', 'минут') . ' назад';
        } else {
            return $hour . ' ' . get_noun_plural_form($hour, 'час', 'часа', 'часов') . ' назад';
        }
    } elseif (($hour >= $hour_now) && ($hour <= $hour_now + 24)) {
        return 'Вчера в ' . $hours_minutes;
    } else {
        return null;
    }
}

/**
 * Показывает css-классы в зависимости от статуса ставки
 *
 * @param array $bet_id_and_price
 * @param array $winning_bets
 * @return array классы, которые нужно показать
 */
function show_classes(array $bet_id_and_price, array $winning_bets): array
{
    $class = [];
    foreach ($winning_bets as $bet) {
        if (htmlspecialchars($bet_id_and_price['id']) == htmlspecialchars($bet['ended'])) {
            if (htmlspecialchars($bet_id_and_price['price']) == htmlspecialchars($bet['max_price'])) {
                $class['item-end'] = 'rates__item--win';
                $class['timer-end'] = 'timer--win';
            } else {
                $class['item-end'] = 'rates__item--end';
                $class['timer-end'] = 'timer--end';
            }
        }
    }
    return array_filter($class);
}

/**
 * pagination_query разбивает выдачу по запросу на несколько страниц, в зависимости от величины выдачи
 *
 * @param mixed $connection
 * @param array $query запрос, где уже есть количество единиц выдачи, и куда добавится информация о лотах на одной странице
 * @param string $sql_query большой запрос к базе, к которому добавим лимит и на сколько позиций сдвигать
 * @param mixed $param параметр запроса для формирования подготовленного запроса, может быть null
 * @return array
 */
function pagination_query(mysqli $connection, array $query, string $sql_query, ?string $param): array
{

    $offset = (CUR_PAGE - 1) * PAGE_ITEMS;
    $sql_query .= " LIMIT ? OFFSET ?";
    $prepared_data = [$param, PAGE_ITEMS, $offset];
    if (is_null($param)) {
        array_shift($prepared_data);
    }
    $stmt = db_get_prepare_stmt($connection, $sql_query, $prepared_data);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $query['list'] = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    return $query['list'];
}

/**
 * @param string $url
 * @return string
 */
function backward(string $url): string
{
    if (CUR_PAGE == 1) {
        $backward_string = $url;
    } else {
        $backward_string = $url . "&page=" . (CUR_PAGE - 1);
    }
    return $backward_string;
}

/**
 * @param string $url
 * @param int $pages_count
 * @return string
 */
function forward(string $url, int $pages_count): string
{
    if (CUR_PAGE == $pages_count) {
        $forward_string = $url;
    } else {
        $forward_string = $url . "&page=" . (CUR_PAGE + 1);
    }
    return $forward_string;
}
