<?php

/**
 * Проверяет длину введенного текста
 *
 * @param mixed $name текст
 * @param mixed $min минимальная длина
 * @param mixed $max максимальная длина
 * @return string|null сообщение об ошибке, если текст не отвечает условию
 */
function validate_length($name, $min, $max)
{
    $len = strlen($name);

    if ($len < $min or $len > $max) {
        return 'Значение должно быть от $min до $max символов';
    }
    return null;
}

/**
 * Проверяет, что ввели число и оно больше нуля
 *
 * @param mixed $name
 * @return string|null сообщение об ошибке
 */
function validate_numeric($name)
{
    if (!is_numeric($name)) {
        return 'Введите число';
    } elseif (abs($name) != $name) {
        return 'Число должно быть больше нуля';
    }
    return null;
}

/**
 * Проверяет, что введенная дата отвечает условию — на день больше момента заполнения формы
 *
 * @param mixed $name
 * @return string|null сообщение об ошибке
 */
function validate_date($name)
{
    $date_tomorrow = date_create('tomorrow');
    $date_ending = date_create($name);
    if ($date_ending < $date_tomorrow) {
        return 'Торги должны длиться как минимум 1 день начиная с сегодня';
    }
    return null;
}

/**
 * Проверяет, есть ли введенная категория в списке
 *
 * @param string $cat_name
 * @param array $allowed_list
 * @return string|null сообщение об ошибке
 */
function validate_category($cat_name, $allowed_list)
{
    if (!in_array($cat_name, $allowed_list)) {
        return 'Укажите категорию';
    }
    return null;
}

/**
 * validate_form_user_data проверяет форму регистрации на ошибки
 *
 * @param mysqli $connection связь с базой данных
 * @param mixed $new_user массив с данными из формы регистрации нового пользователя
 * @return array массив с ошибками
 */
function validate_form_user_data(mysqli $connection, array $new_user): array
{
    $errors = [];
    $required = ['new-user-email', 'new-user-password', 'new-user-name', 'new-user-contact'];

    $rules = [
        'new-user-email' => function ($value) {
            return validate_length($value, 5, 64);
        },
        'new-user-password' => function ($value) {
            return validate_length($value, 3, 64);
        },
        'new-user-name' => function ($value) {
            return validate_length($value, 2, 64);
        },
        'new-user-contact' => function ($value) {
            return validate_length($value, 2, 500);
        }
    ];

    foreach ($new_user as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }

        if (in_array($key, $required) && empty($value)) {
            $errors[$key] = 'Это поле надо заполнить';
        }

    }
    if (empty($errors['new-user-email'])) {
        $errors['new-user-email'] = validate_email($connection, $new_user['new-user-email']);
    }
    return array_filter($errors);
}

/**
 * validate_email проверяет, есть ли в базе этот имейл
 *
 * @param mixed $connection соединение с базой данных
 * @param string $new_user_email электронная почта нового юзера до проверки
 * @return bool
 */
function validate_email(mysqli $connection, string $new_user_email)
{
    $email = mysqli_real_escape_string($connection, $new_user_email);
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $res = mysqli_query($connection, $sql);
    if (mysqli_num_rows($res) > 0) {
        return 'Пользователь с этим email уже зарегистрирован';
    }
    return null;
}

/**
 * Проверяет введенную ставку на корректность
 *
 * @param mixed $add_bet новая ставка
 * @param int $min_bet минимальная ставка
 * @return array массив с ошибками
 */
function validate_new_bet($add_bet, int $min_bet): array
{
    $errors = [];

    if (empty($add_bet)) {
        $errors['new-bet'] = 'Это поле надо заполнить';
    } elseif (is_numeric($add_bet) and $add_bet > 0 and $add_bet < $min_bet) {
        $errors['new-bet'] = 'Минимальная ставка ' . $min_bet;
    } else {
        $errors['new-bet'] = validate_numeric($add_bet);
    }

    return array_filter($errors);
}

/**
 * Проверяет загруженный файл и дает ему имя на сервере. Возвращает массив с данными нового лота
 *
 * @param array $errors Массив с ошибками формы
 * @param array $uploading Массив с данными загружаемого файла
 * @param array $add_lot Массив с данными нового лота
 * @return array Массив с данными нового лота
 */
function validate_file(array &$errors, array $uploading, array $add_lot): array
{
    if (!$uploading['success']) {
        $errors['lot-img'] = $uploading['error'];
    }
    $add_lot['lot-img'] = $uploading['upload_name'];

    return $add_lot;
}

/**
 * Проверяет на ошибки форму добавления нового лота
 *
 * @param array $add_lot Данные нового лота из формы
 * @param array $cats_ids Массив с id категорий
 * @return array Ошибки
 */
function validate_form_data(array $add_lot, array $cats_ids): array
{

    $required = ['lot-name', 'lot-category', 'lot-description', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

    $rules = [
        'lot-name' => function ($value) {
            return validate_length($value, 5, 200);
        },
        'lot-category' => function ($value) use ($cats_ids) {
            return validate_category($value, $cats_ids);
        },
        'lot-description' => function ($value) {
            return validate_length($value, 5, 3000);
        },
        'lot-rate' => function ($value) {
            return validate_numeric($value);
        },
        'lot-step' => function ($value) {
            return validate_numeric($value);
        },
        'lot-date' => function ($value) {
            return validate_date($value);
        }
    ];

    foreach ($add_lot as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }

        if (in_array($key, $required) && empty($value)) {
            $errors[$key] = 'Это поле надо заполнить';
        }
    }

    return array_filter($errors);
}

