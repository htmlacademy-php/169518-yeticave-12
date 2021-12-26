<?php 

function validate_length($name, $min, $max) {
    $len = strlen($name);

    if ($len < $min or $len > $max) {
        return 'Значение должно быть от $min до $max символов';
    }
    return null;
}

function validate_numeric($name) {

    if (!is_numeric($name)) {
            return 'Введите число';
        }
    elseif (abs($name) != $name) {
            return 'Число должно быть больше нуля';
        }  
    return null;
}

function validate_date($name) {
    $date_tomorrow = date_create('tomorrow');
    $date_ending = date_create($name);
   if($date_ending < $date_tomorrow) {
    return 'Торги должны длиться как минимум 1 день начиная с сегодня';
   }
   return null;
}

function validate_category($cat_name, $allowed_list) {
    if (!in_array($cat_name, $allowed_list)) {
        return 'Укажите категорию';
    }
    return null;
}



