<?php

require_once('src/session.php');

/**
 * @file
 *
 * File contains functions for work with MySql databsase
 */

define('DATABASE_HOST', 'localhost');
define('DATABASE_BASE_NAME', 'yeticave');
define('DATABASE_USER', 'root');
define('DATABASE_PASSWORD', '');
define('UPLOAD_MAX_SIZE', 2097152);


/**
 * Connect to DB
 *
 * @return mysqli
 */
function database_get_connection(): mysqli
{
    $base = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_BASE_NAME);
    mysqli_set_charset($base, "utf8");
    if (!$base) {
        echo "Ошибка подключения к БД. Код ошибки: " . mysqli_connect_error();
        exit();
    }

    return $base;
}

