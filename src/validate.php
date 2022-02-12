<?php 

/**
 * Проверяет длину введенного текста
 *
 * @param  mixed $name текст
 * @param  mixed $min минимальная длина
 * @param  mixed $max максимальная длина
 * @return сообщение об ошибке, если текст не отвечает условию, или null
 */
function validate_length($name, $min, $max) {
    $len = strlen($name);

    if ($len < $min or $len > $max) {
        return 'Значение должно быть от $min до $max символов';
    }
    return null;
}

/**
 * Проверяет, что ввели число и оно больше нуля
 *
 * @param  mixed $name
 * @return сообщение об ошибке или null
 */
function validate_numeric($name) {

    if (!is_numeric($name)) {
            return 'Введите число';
        }
    elseif (abs($name) != $name) {
            return 'Число должно быть больше нуля';
        }  
    return null;
}

/**
 * Проверяет, что введенная дата отвечает условию — на день больше момента заполнения формы
 *
 * @param  mixed $name
 * @return сообщение об ошибке или null
 */
function validate_date($name) {
    $date_tomorrow = date_create('tomorrow');
    $date_ending = date_create($name);
   if($date_ending < $date_tomorrow) {
    return 'Торги должны длиться как минимум 1 день начиная с сегодня';
   }
   return null;
}

/**
 * Проверяет, есть ли введенная категория в списке
 *
 * @param  string $cat_name
 * @param  array $allowed_list
 * @return сообщение об ошибке или null
 */
function validate_category($cat_name, $allowed_list) {
    if (!in_array($cat_name, $allowed_list)) {
        return 'Укажите категорию';
    }
    return null;
}



